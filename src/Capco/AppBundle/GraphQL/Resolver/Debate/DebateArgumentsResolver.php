<?php

namespace Capco\AppBundle\GraphQL\Resolver\Debate;

use Capco\AppBundle\Entity\Debate\Debate;
use Capco\AppBundle\Repository\DebateArgumentRepository;
use Capco\UserBundle\Entity\User;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class DebateArgumentsResolver implements ResolverInterface
{
    public const ORDER_PUBLISHED_AT = 'PUBLISHED_AT';
    public const ORDER_VOTE_COUNT = 'VOTE_COUNT';

    private DebateArgumentRepository $repository;

    public function __construct(DebateArgumentRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(Debate $debate, Argument $args, ?User $viewer): ConnectionInterface
    {
        $filters = self::getFilters($args, $viewer);
        $orderBy = self::getOrderBy($args);

        $paginator = new Paginator(function (int $offset, int $limit) use (
            $debate,
            $filters,
            $orderBy
        ) {
            if (0 === $offset && 0 === $limit) {
                return [];
            }

            return $this->repository
                ->getByDebate($debate, $limit, $offset, $filters, $orderBy)
                ->getIterator()
                ->getArrayCopy();
        });
        $totalCount = $this->repository->countByDebate($debate, $filters);

        return $paginator->auto($args, $totalCount);
    }

    private static function getFilters(Argument $args, ?User $viewer): array
    {
        $filters = [];
        if ($args->offsetExists('value')) {
            $filters['value'] = $args->offsetGet('value');
        }
        if (null === $viewer || !$viewer->isAdmin() || !($args->offsetGet('includeUnpublished'))) {
            $filters['publishedOnly'] = true;
        }

        return $filters;
    }

    private static function getOrderBy(Argument $args): ?array
    {
        $orderByFields = [
            'PUBLISHED_AT' => 'publishedAt',
            'VOTE_COUNT' => 'votesCount'
        ];
        $orderBy = $args->offsetGet('orderBy');
        if ($orderBy) {
            $orderBy['field'] = $orderByFields[$orderBy['field']];
        }
        return $orderBy;
    }
}