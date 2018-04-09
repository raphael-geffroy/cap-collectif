<?php

namespace Capco\AppBundle\Repository;

use Capco\AppBundle\Entity\Proposal;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class FollowerRepository extends EntityRepository
{
    public function countProposalFollower(Proposal $proposal, User $user = null): int
    {
        $query = $this->createQueryBuilder('f')
            ->select('count(f.id)')
            ->join('f.proposal', 'p')
            ->join('f.user', 'u')
            ->andWhere('p.id = :proposalId')
            ->setParameter('proposalId', $proposal->getId());

        if (null !== $user) {
            $query->andWhere('u.id = :userId')
                ->setParameter('userId', $user->getId());
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    public function getByCriteriaOrdered(array $criteria, array $orderBy, $limit = 32, $offset = 0): Paginator
    {
        $qb = $this->createQueryBuilder('f')
            ->join('f.proposal', 'p')
            ->join('f.user', 'u');

        if (isset($criteria['proposal'])) {
            $qb
                ->andWhere('p.id = :proposalId')
                ->setParameter('proposalId', $criteria['proposal']->getId());
        }

        $sortField = array_keys($orderBy)[0];
        $direction = $orderBy[$sortField];

        switch ($sortField) {
            case 'NAME':
            case 'USERNAME':
                $qb
                    ->addOrderBy('u.username', $direction);
                break;
            case 'RANDOM':
                $qb
                    ->addSelect('RAND() as HIDDEN rand')
                    ->addOrderBy('rand');
                break;
            default:
                $qb
                    ->addOrderBy('u.username', $direction);
                break;
        }
        $query = $qb->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->useQueryCache(true)// ->useResultCache(true, 60)
        ;

        return new Paginator($query);
    }
}
