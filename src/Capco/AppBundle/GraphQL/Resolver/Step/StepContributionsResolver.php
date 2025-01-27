<?php

namespace Capco\AppBundle\GraphQL\Resolver\Step;

use Capco\AppBundle\Entity\Steps\AbstractStep;
use Capco\AppBundle\GraphQL\DataLoader\Step\StepContributionsDataLoader;
use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\PromiseAdapter\PromiseAdapterInterface;

class StepContributionsResolver implements ResolverInterface
{
    private $dataLoader;
    private $promiseAdapter;

    public function __construct(
        StepContributionsDataLoader $dataLoader,
        PromiseAdapterInterface $promiseAdapter
    ) {
        $this->dataLoader = $dataLoader;
        $this->promiseAdapter = $promiseAdapter;
    }

    public function __invoke(AbstractStep $step, Argument $args): Promise
    {
        return $this->dataLoader->load(compact('step', 'args'));
    }

    public function resolveSync(AbstractStep $step, Argument $args): Connection
    {
        $connection = null;
        $this->promiseAdapter->await(
            $this->__invoke($step, $args)->then(static function ($value) use (&$connection) {
                $connection = $value;
            })
        );

        return $connection;
    }
}
