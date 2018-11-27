<?php
namespace spec\Capco\AppBundle\Elasticsearch;

use Prophecy\Argument;
use PhpSpec\ObjectBehavior;
use Swarrot\Broker\Message;
use Capco\AppBundle\Entity\Event;
use Capco\UserBundle\Entity\User;
use Capco\AppBundle\Entity\Comment;
use Capco\AppBundle\Entity\Proposal;
use Swarrot\SwarrotBundle\Broker\Publisher;
use Capco\AppBundle\Entity\ProposalCollectVote;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Capco\AppBundle\Elasticsearch\ElasticsearchDoctrineListener;
use Doctrine\ORM\Events;

class ElasticsearchDoctrineListenerSpec extends ObjectBehavior
{
    function let(Publisher $publisher)
    {
        $this->beConstructedWith($publisher);
    }

    function it_subscribe_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            Events::postPersist,
            Events::postUpdate,
            Events::preRemove,
        ]);
    }

    function it_index_an_event(
        Publisher $publisher,
        LifecycleEventArgs $args,
        Event $event,
        User $author
    ) {
        $event->getId()->willReturn('event1');
        $event->getAuthor()->willReturn($author);

        $message = new Message(
            json_encode([
                'class' => get_class($event->getWrappedObject()),
                'id' => 'event1',
            ])
        );
        $publisher
            ->publish("elasticsearch.indexation", Argument::exact($message))
            ->shouldBeCalledOnce();

        $args->getObject()->willReturn($event);
        $this->handleEvent($args);
    }

    function it_index_a_proposal(
        Publisher $publisher,
        LifecycleEventArgs $args,
        Proposal $proposal,
        User $author
    ) {
        $proposalMessage = new Message(
            json_encode([
                'class' => get_class($proposal->getWrappedObject()),
                'id' => 'proposal1',
            ])
        );
        $authorMessage = new Message(
            json_encode(['class' => get_class($author->getWrappedObject()), 'id' => 'user1'])
        );
        $publisher
            ->publish("elasticsearch.indexation", Argument::exact($proposalMessage))
            ->shouldBeCalledOnce();
        $publisher
            ->publish("elasticsearch.indexation", Argument::exact($authorMessage))
            ->shouldBeCalledOnce();
        $proposal->getId()->willReturn('proposal1');
        $author->getId()->willReturn('user1');

        $proposal->getAuthor()->willReturn($author);
        $args->getObject()->willReturn($proposal);
        $this->handleEvent($args);
    }

    function it_index_a_proposal_vote(
        Publisher $publisher,
        LifecycleEventArgs $args,
        ProposalCollectVote $vote,
        Proposal $proposal,
        User $voteAuthor
    ) {
        $proposalMessage = new Message(
            json_encode([
                'class' => get_class($proposal->getWrappedObject()),
                'id' => 'proposal1',
            ])
        );
        $voteAuthorMessage = new Message(
            json_encode([
                'class' => get_class($voteAuthor->getWrappedObject()),
                'id' => 'user1',
            ])
        );

        // Votes are not indexed
        $publisher
            ->publish("elasticsearch.indexation", Argument::exact($proposalMessage))
            ->shouldBeCalledOnce();
        $publisher
            ->publish("elasticsearch.indexation", Argument::exact($voteAuthorMessage))
            ->shouldBeCalledOnce();
        $proposal->getId()->willReturn('proposal1');
        $voteAuthor->getId()->willReturn('user1');

        $vote->getRelated()->willReturn($proposal);
        $vote->getAuthor()->willReturn($voteAuthor);

        $args->getObject()->willReturn($vote);
        $this->handleEvent($args);
    }

    function it_index_a_comment(
        Publisher $publisher,
        LifecycleEventArgs $args,
        Comment $comment,
        Proposal $commentProposal,
        User $commentAuthor
    ) {
        $commentMessage = new Message(
            json_encode([
                'class' => get_class($comment->getWrappedObject()),
                'id' => 'comment1',
            ])
        );
        $commentProposalMessage = new Message(
            json_encode([
                'class' => get_class($commentProposal->getWrappedObject()),
                'id' => 'proposal1',
            ])
        );
        $commentAuthorMessage = new Message(
            json_encode([
                'class' => get_class($commentAuthor->getWrappedObject()),
                'id' => 'user1',
            ])
        );

        $publisher
            ->publish("elasticsearch.indexation", Argument::exact($commentMessage))
            ->shouldBeCalledOnce();
        $publisher
            ->publish("elasticsearch.indexation", Argument::exact($commentProposalMessage))
            ->shouldBeCalledOnce();
        $publisher
            ->publish("elasticsearch.indexation", Argument::exact($commentAuthorMessage))
            ->shouldBeCalledOnce();

        $comment->getId()->willReturn('comment1');
        $commentProposal->getId()->willReturn('proposal1');

        $comment->getRelatedObject()->willReturn($commentProposal);

        $commentAuthor->getId()->willReturn('user1');
        $comment->getAuthor()->willReturn($commentAuthor);

        $args->getObject()->willReturn($comment);
        $this->handleEvent($args);
    }
}
