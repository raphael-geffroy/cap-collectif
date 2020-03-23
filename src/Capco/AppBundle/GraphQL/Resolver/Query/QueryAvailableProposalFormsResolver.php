<?php

namespace Capco\AppBundle\GraphQL\Resolver\Query;

use Capco\AppBundle\Repository\ProposalFormRepository;
use Overblog\GraphQLBundle\Definition\Argument as Arg;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class QueryAvailableProposalFormsResolver implements ResolverInterface
{
    private $repository;

    public function __construct(ProposalFormRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(): array
    {
        return $this->repository->findBy(['step' => null]);
    }
}