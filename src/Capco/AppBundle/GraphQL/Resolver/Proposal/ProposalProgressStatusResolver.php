<?php

namespace Capco\AppBundle\GraphQL\Resolver\Proposal;

use Capco\AppBundle\Entity\Proposal;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class ProposalProgressStatusResolver implements ResolverInterface
{
    public function __invoke(Proposal $proposal): string
    {
        return $proposal->getGlobalProgressStatus();
    }
}