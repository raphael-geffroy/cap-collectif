<?php

namespace Capco\AppBundle\GraphQL\Resolver\Step;

use Capco\AppBundle\Entity\Steps\AbstractStep;
use Capco\AppBundle\Helper\StepHelper;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class StepStateResolver implements ResolverInterface
{
    private $stepHelper;

    public function __construct(StepHelper $stepHelper)
    {
        $this->stepHelper = $stepHelper;
    }

    public function __invoke(AbstractStep $step): string
    {
        return AbstractStep::$stepStates[$this->stepHelper->getStatus($step)];
    }
}
