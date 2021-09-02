<?php

namespace Capco\AppBundle\Repository;

use Capco\AppBundle\Entity\SiteParameter;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method SiteParameter|null findOneBy(array $criteria, array $orderBy = null)
 */
class SiteParameterRepository extends EntityRepository
{
    public const REGISTRATION_PAGE_CODE_KEYNAME = 'registration.customcode';

    public function getValuesIfEnabled(string $locale): array
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->from($this->getClassName(), 'p', 'p.keyname')
            ->leftJoin('p.translations', 't', Join::WITH, 't.locale = :locale')
            ->select('p', 't')
            ->andWhere('p.isEnabled = 1')
            ->groupBy('p.keyname')
            ->setParameter('locale', $locale)
            ->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true, 60)
            ->getResult();
    }

    public function getValue(string $keyname, string $locale): ?string
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->from($this->getClassName(), 'p')
            ->andWhere('p.isEnabled = 1')
            ->andWhere('p.keyname = :keyname')
            ->setParameter('keyname', $keyname);

        if (in_array($keyname, SiteParameter::NOT_TRANSLATABLE)) {
            $qb->select('p.value');
        } else {
            $qb->select('t.value')
                ->leftJoin('p.translations', 't', Join::WITH, 't.locale = :locale')
                ->setParameter('locale', $locale);
        }

        return $qb
            ->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true, 60)
            ->getSingleResult()['value'];
    }
}
