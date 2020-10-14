<?php

namespace Capco\AppBundle\Elasticsearch;

use Capco\AppBundle\Entity\AbstractVote;
use Capco\AppBundle\Entity\Comment;
use Capco\AppBundle\Entity\Event;
use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\ProposalAnalysis;
use Capco\AppBundle\Entity\ProposalAnalyst;
use Capco\AppBundle\Entity\ProposalAssessment;
use Capco\AppBundle\Entity\ProposalDecision;
use Capco\AppBundle\Entity\ProposalDecisionMaker;
use Capco\AppBundle\Entity\ProposalSupervisor;
use Capco\AppBundle\Model\Contribution;
use Capco\AppBundle\Model\HasAuthorInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Doctrine\Common\Util\ClassUtils;

/**
 *  Listen any persist, update or delete operations happening in Doctrine, in order to:
 * - Index any created or updated object
 * - Re-index related author / step / project, to update counters.
 * - De-index any deleted object.
 *
 * All indexations are added to a RabbitMQ queue, because doing all of this synchronously
 * would be very expensive.
 */
class ElasticsearchDoctrineListener implements EventSubscriber
{
    private $logger;
    private $elasticsearchRabbitMQListener;

    public function __construct(
        ElasticsearchRabbitMQListener $elasticsearchRabbitMQListener,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->elasticsearchRabbitMQListener = $elasticsearchRabbitMQListener;
    }

    public function getSubscribedEvents(): array
    {
        return [Events::postPersist, Events::postUpdate, Events::preRemove];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->process($args->getObject());
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->process($args->getObject());
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $this->process($args->getObject());
    }

    public function handleEvent(LifecycleEventArgs $args): void
    {
        $this->process($args->getObject());
    }

    public function addToMessageStack(IndexableInterface $entity): void
    {
        $body = json_encode([
            'class' => ClassUtils::getClass($entity),
            'id' => $entity->getId(),
        ]);
        $this->logger->info(
            '[elastic_search_doctrine_listener] Adding new message to stack ' . $body
        );

        // We cannot dinamicly call static methods with phpspec, so we hardcode the priority to 1.
        if (false !== strpos(\get_class($entity), 'Double')) {
            $priority = 1;
        } else {
            $priority = $entity::getElasticsearchPriority();
        }
        $this->elasticsearchRabbitMQListener->addToMessageStack(new Message($body), $priority);
    }

    public function process($entity, bool $indexAuthor = true, bool $skipProcess = false): void
    {
        if ($entity instanceof IndexableInterface) {
            $this->addToMessageStack($entity);
        }
        if (
            $indexAuthor &&
            ($entity instanceof HasAuthorInterface ||
                ($entity instanceof Contribution && method_exists($entity, 'getAuthor'))) &&
            $entity->getAuthor()
        ) {
            $this->addToMessageStack($entity->getAuthor());
        }
        if (
            $entity instanceof ProposalAnalyst ||
            $entity instanceof ProposalDecisionMaker ||
            $entity instanceof ProposalSupervisor ||
            $entity instanceof ProposalAssessment ||
            $entity instanceof ProposalDecision ||
            $entity instanceof ProposalAnalysis
        ) {
            $this->process($entity->getProposal(), false);
        }
        if ($entity instanceof Comment && $entity->getRelatedObject()) {
            $this->process($entity->getRelatedObject(), false, true);
        }
        if ($entity instanceof AbstractVote && $entity->getRelated()) {
            if ($entity->getRelated() instanceof Proposal) {
                $this->process($entity->getRelated(), false, true);
            } else {
                $this->process($entity->getRelated(), false);
            }
        }
        if ($entity instanceof Event && $entity->getProjects()->count() > 0) {
            foreach ($entity->getProjects() as $project) {
                $this->process($project, false);
            }
        }
        if (!$skipProcess && $entity instanceof Proposal) {
            if (($comments = $entity->getComments()) && $comments->count() > 0) {
                foreach ($comments as $comment) {
                    $this->process($comment, false);
                }
            }

            if (
                !empty(
                    ($votes = array_merge(
                        $entity->getSelectionVotes()->toArray(),
                        $entity->getCollectVotes()->toArray()
                    ))
                )
            ) {
                foreach ($votes as $vote) {
                    $this->process($vote, false);
                }
            }
        }
    }
}
