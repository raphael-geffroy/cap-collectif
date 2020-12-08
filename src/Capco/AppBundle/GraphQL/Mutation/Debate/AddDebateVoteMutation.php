<?php

namespace Capco\AppBundle\GraphQL\Mutation\Debate;

use Psr\Log\LoggerInterface;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Capco\AppBundle\Entity\Debate\Debate;
use Doctrine\DBAL\Driver\DriverException;
use Overblog\GraphQLBundle\Error\UserError;
use Capco\AppBundle\Entity\Debate\DebateVote;
use Overblog\GraphQLBundle\Relay\Node\GlobalId;
use Capco\AppBundle\Repository\DebateVoteRepository;
use Capco\AppBundle\GraphQL\Resolver\GlobalIdResolver;
use Overblog\GraphQLBundle\Definition\Argument as Arg;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class AddDebateVoteMutation implements MutationInterface
{
    public const UNKNOWN_DEBATE = 'UNKNOWN_DEBATE';
    public const CLOSED_DEBATE = 'CLOSED_DEBATE';

    private EntityManagerInterface $em;
    private LoggerInterface $logger;
    private GlobalIdResolver $globalIdResolver;
    private DebateVoteRepository $repository;

    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        GlobalIdResolver $globalIdResolver,
        DebateVoteRepository $repository
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->globalIdResolver = $globalIdResolver;
        $this->repository = $repository;
    }

    public function __invoke(Arg $input, User $viewer): array
    {
        $debateId = $input->offsetGet('debateId');
        $debate = $this->globalIdResolver->resolve($debateId, $viewer);

        if (!$debate || !$debate instanceof Debate) {
            $this->logger->error('Unknown argument `debateId`.', ['id' => $debateId]);

            return $this->generateErrorPayload(self::UNKNOWN_DEBATE);
        }

        if (!$debate->viewerCanParticipate($viewer)) {
            $this->logger->error('The debate is not open.', ['id' => $debateId]);

            return $this->generateErrorPayload(self::CLOSED_DEBATE);
        }

        $value = $input->offsetGet('value');
        $debateVote = (new DebateVote())
            ->setDebate($debate)
            ->setValue($value)
            ->setUser($viewer);

        $previousVote = $this->repository->getOneByDebateAndUser($debate, $viewer);
        $previousVoteId = null;
        if ($previousVote) {
            $previousVoteId = $previousVote->getId();
            $this->em->remove($previousVote);
        }
        $this->em->persist($debateVote);

        try {
            $this->em->flush();
        } catch (DriverException $e) {
            $this->logger->error(
                __METHOD__ . ' => ' . $e->getErrorCode() . ' : ' . $e->getMessage()
            );

            throw new UserError('Internal error, please try again.');
        }

        return $this->generateSuccessFulPayload($debateVote, $previousVoteId);
    }

    private function generateSuccessFulPayload(DebateVote $vote, ?string $previousVoteId): array
    {
        return [
            'debateVote' => $vote,
            'previousVoteId' => $previousVoteId
                ? GlobalId::toGlobalId('DebateVote', $previousVoteId)
                : null,
            'errorCode' => null,
        ];
    }

    private function generateErrorPayload(string $message): array
    {
        return ['debateVote' => null, 'previousVoteId' => null, 'errorCode' => $message];
    }
}