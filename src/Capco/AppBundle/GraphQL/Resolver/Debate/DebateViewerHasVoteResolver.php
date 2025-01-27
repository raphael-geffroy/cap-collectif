<?php

namespace Capco\AppBundle\GraphQL\Resolver\Debate;

use Capco\AppBundle\Entity\Debate\Debate;
use Capco\AppBundle\GraphQL\Resolver\Traits\ResolverTrait;
use Capco\AppBundle\Repository\DebateVoteRepository;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class DebateViewerHasVoteResolver implements ResolverInterface
{
    use ResolverTrait;
    private DebateVoteRepository $repository;

    public function __construct(DebateVoteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(Debate $debate, $viewer): bool
    {
        $this->preventNullableViewer($viewer);

        return null !== $this->repository->getOneByDebateAndUser($debate, $viewer);
    }
}
