<?php

namespace Capco\AppBundle\GraphQL\Resolver\Query;

use Capco\AppBundle\Entity\UserInvite;
use Capco\AppBundle\GraphQL\ConnectionBuilder;
use Capco\AppBundle\Repository\UserInviteRepository;
use Capco\UserBundle\Entity\User;
use Capco\UserBundle\Repository\UserRepository;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;

class UserInvitationsAvailabilitySearchQueryResolver implements ResolverInterface
{
    private UserRepository $userRepository;
    private UserInviteRepository $userInviteRepository;
    private ConnectionBuilder $builder;

    public function __construct(
        ConnectionBuilder $builder,
        UserRepository $userRepository,
        UserInviteRepository $userInviteRepository
    ) {
        $this->userRepository = $userRepository;
        $this->userInviteRepository = $userInviteRepository;
        $this->builder = $builder;
    }

    public function __invoke(Argument $args): ConnectionInterface
    {
        $emails = $args->offsetGet('emails');
        $existingUsers = $this->userRepository->findBy(['email' => $emails]);
        $nonAvailableUsersEmails = array_map(static function (User $user) {
            return $user->getEmail();
        }, $existingUsers);
        $existingInvitations = $this->userInviteRepository->findBy(['email' => $emails]);
        $nonAvailableInvitationEmails = array_map(static function (UserInvite $userInvite) {
            return $userInvite->getEmail();
        }, $existingInvitations);

        $payload = [];

        foreach ($emails as $email) {
            if (\in_array($email, $nonAvailableUsersEmails, true)) {
                $payload[] = [
                    'email' => $email,
                    'availableForUser' => false,
                    'availableForInvitation' => false,
                ];

                continue;
            }

            if (\in_array($email, $nonAvailableInvitationEmails, true)) {
                $payload[] = [
                    'email' => $email,
                    'availableForUser' => true,
                    'availableForInvitation' => false,
                ];
            }
        }

        $connection = $this->builder->connectionFromArray($payload);
        $connection->setTotalCount(\count($payload));

        return $connection;
    }
}
