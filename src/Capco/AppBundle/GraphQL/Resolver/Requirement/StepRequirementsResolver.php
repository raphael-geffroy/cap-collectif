<?php

namespace Capco\AppBundle\GraphQL\Resolver\Requirement;

use Capco\AppBundle\Entity\Steps\AbstractStep;
use Capco\AppBundle\Entity\Steps\CollectStep;
use Capco\AppBundle\Entity\Steps\ConsultationStep;
use Capco\AppBundle\Entity\Steps\QuestionnaireStep;
use Capco\AppBundle\Entity\Steps\SelectionStep;
use Capco\AppBundle\GraphQL\ConnectionBuilder;
use Capco\AppBundle\Repository\RequirementRepository;
use Capco\UserBundle\Entity\User;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;

class StepRequirementsResolver implements ResolverInterface
{
    private RequirementRepository $repository;
    private ViewerMeetsTheRequirementResolver $viewerMeetsTheRequirementResolver;
    private ConnectionBuilder $builder;

    public function __construct(
        RequirementRepository $repository,
        ViewerMeetsTheRequirementResolver $viewerMeetsTheRequirementResolver,
        ConnectionBuilder $builder
    ) {
        $this->repository = $repository;
        $this->builder = $builder;
        $this->viewerMeetsTheRequirementResolver = $viewerMeetsTheRequirementResolver;
    }

    public function __invoke(
        AbstractStep $step,
        // User|string

        $user,
        Argument $args
    ): ConnectionInterface {
        $requirements = $this->repository->getByStep($step);

        $connection = $this->builder->connectionFromArray($requirements, $args);
        $connection->setTotalCount(\count($requirements));

        if (
            $step instanceof QuestionnaireStep
            || $step instanceof SelectionStep
            || $step instanceof CollectStep
            || $step instanceof ConsultationStep
        ) {
            $connection->{'reason'} = $step->getRequirementsReason();
        }
        $connection->{'viewerMeetsTheRequirements'} = false;

        if ($user instanceof User) {
            $connection->{'viewerMeetsTheRequirements'} = $this->viewerMeetsTheRequirementsResolver(
                $user,
                $step
            );
        }

        return $connection;
    }

    public function viewerMeetsTheRequirementsResolver(User $user, AbstractStep $step): bool
    {
        $requirements = $this->repository->getByStep($step);
        foreach ($requirements as $requirement) {
            if (!$this->viewerMeetsTheRequirementResolver->__invoke($requirement, $user)) {
                return false;
            }
        }

        return true;
    }
}
