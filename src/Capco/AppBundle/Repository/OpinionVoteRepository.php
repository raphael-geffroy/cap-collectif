<?php

namespace Capco\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * OpinionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class OpinionVoteRepository extends EntityRepository
{
    public function getByOpinion($opinion)
    {
        $qb = $this->createQueryBuilder('v')
                ->leftJoin('v.opinion', 'o')
                ->andWhere('o.slug = :slug')
                ->setParameter('slug', $opinion);

        return $qb->getQuery()
            ->getResult();
    }

    private function deleteCollections($em, $init, $final)
    {
        if (empty($init)) {
            return;
        }

        if (!$final->getAddresses() instanceof \Doctrine\ORM\PersistentCollection) {
            foreach ($init['addresses'] as $addr) {
                $em->remove($addr);
            }
        }
    }
}
