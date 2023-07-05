<?php

namespace Capco\AppBundle\GraphQL\Resolver;

use Capco\AppBundle\Entity\Steps\AbstractStep;
use Capco\AppBundle\GraphQL\DataLoader\User\ViewerProposalVotesDataLoader;
use Capco\AppBundle\GraphQL\Resolver\Traits\ResolverTrait;
use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class ViewerStepVotesResolver implements ResolverInterface
{
    use ResolverTrait;

    private ViewerProposalVotesDataLoader $dataLoader;

    public function __construct(ViewerProposalVotesDataLoader $dataLoader)
    {
        $this->dataLoader = $dataLoader;
    }

    public function __invoke(AbstractStep $step, $user, Argument $args): Promise
    {
        $args->offsetSet('stepId', $step->getId());

        return $this->dataLoader->load(compact('user', 'args'));
    }
}
