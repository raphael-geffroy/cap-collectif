<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\Notifier\UserNotifier;
use Swarrot\Broker\Message;
use Capco\AppBundle\Entity\Reply;
use Capco\UserBundle\Entity\User;
use Capco\AppBundle\Form\ReplyType;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Swarrot\SwarrotBundle\Broker\Publisher;
use Capco\AppBundle\Helper\RedisStorageHelper;
use Capco\AppBundle\Helper\ResponsesFormatter;
use Capco\AppBundle\Repository\ReplyRepository;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Node\GlobalId;
use Symfony\Component\Form\FormFactoryInterface;
use Capco\AppBundle\GraphQL\Exceptions\GraphQLException;
use Capco\AppBundle\Notifier\QuestionnaireReplyNotifier;
use Capco\AppBundle\GraphQL\Resolver\Step\StepUrlResolver;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class UpdateReplyMutation implements MutationInterface
{
    private $em;
    private $formFactory;
    private $redisStorageHelper;
    private $responsesFormatter;
    private $replyRepo;
    private $userNotifier;
    private $stepUrlResolver;
    private $publisher;

    public function __construct(
        EntityManagerInterface $em,
        FormFactoryInterface $formFactory,
        ReplyRepository $replyRepo,
        RedisStorageHelper $redisStorageHelper,
        ResponsesFormatter $responsesFormatter,
        UserNotifier $userNotifier,
        StepUrlResolver $stepUrlResolver,
        Publisher $publisher
    ) {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->replyRepo = $replyRepo;
        $this->redisStorageHelper = $redisStorageHelper;
        $this->responsesFormatter = $responsesFormatter;
        $this->userNotifier = $userNotifier;
        $this->stepUrlResolver = $stepUrlResolver;
        $this->publisher = $publisher;
    }

    public function __invoke(Argument $input, User $viewer): array
    {
        $values = $input->getRawArguments();
        $replyId = GlobalId::fromGlobalId($values['replyId']);
        /** @var Reply $reply */
        $reply = $this->replyRepo->find($replyId['id']);
        unset($values['replyId']);

        if (!$reply) {
            throw new UserError('Reply not found.');
        }
        $wasDraft = $reply->isDraft();
        $draft = false;
        if (isset($values['draft']) && true === $values['draft']) {
            $draft = true;
        }
        $author = $reply->getAuthor();
        if ($author !== $viewer) {
            throw new UserError('You are not allowed to update this reply.');
        }

        $values['responses'] = $this->responsesFormatter->format($values['responses']);

        $form = $this->formFactory->create(ReplyType::class, $reply, []);
        $form->submit($values, false);
        if (!$form->isValid()) {
            throw GraphQLException::fromFormErrors($form);
        }

        $questionnaire = $reply->getQuestionnaire();

        if (
            $questionnaire &&
            $questionnaire->isAcknowledgeReplies() &&
            !$reply->isDraft() &&
            $questionnaire->getStep()
        ) {
            $step = $questionnaire->getStep();
            $project = $step->getProject();
            $endAt = $step->getEndAt();
            $stepUrl = $this->stepUrlResolver->__invoke($step);
            $isUpdated = $wasDraft && !$draft ? false : true;
            $this->userNotifier->acknowledgeReply(
                $project,
                $reply,
                $endAt,
                $stepUrl,
                $step,
                $viewer,
                $isUpdated
            );
        }

        $this->em->flush();

        if ($questionnaire && !$reply->isDraft() && $questionnaire->isNotifyResponseUpdate()) {
            $this->publisher->publish(
                'questionnaire.reply',
                new Message(
                    json_encode([
                        'replyId' => $reply->getId(),
                        'state' => $wasDraft
                            ? QuestionnaireReplyNotifier::QUESTIONNAIRE_REPLY_CREATE_STATE
                            : QuestionnaireReplyNotifier::QUESTIONNAIRE_REPLY_UPDATE_STATE
                    ])
                )
            );
        }

        $this->redisStorageHelper->recomputeUserCounters($viewer);

        return ['reply' => $reply];
    }
}
