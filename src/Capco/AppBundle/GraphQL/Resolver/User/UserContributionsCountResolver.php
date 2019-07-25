<?php

namespace Capco\AppBundle\GraphQL\Resolver\User;

use Capco\UserBundle\Entity\User;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class UserContributionsCountResolver implements ResolverInterface
{
    protected $userEventCommentsCountResolver;
    protected $userProposalsResolver;

    public function __construct(
        UserEventCommentsCountResolver $userEventCommentsCountResolver,
        UserProposalsResolver $userProposalsResolver
    ) {
        $this->userEventCommentsCountResolver = $userEventCommentsCountResolver;
        $this->userProposalsResolver = $userProposalsResolver;
    }

    public function __invoke(User $user, $viewer): int
    {
        return $this->userEventCommentsCountResolver->__invoke($user) +
            $this->userProposalsResolver->__invoke($viewer, $user)->totalCount +
            $user->getContributionsCount();
    }
}
