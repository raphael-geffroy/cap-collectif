<?php

namespace Capco\AppBundle\Repository;

use Capco\AppBundle\Entity\ConsultationStep;
use Capco\AppBundle\Entity\Opinion;
use Capco\AppBundle\Entity\OpinionVersion;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * SourceRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SourceRepository extends EntityRepository
{
    public function getRecentOrdered()
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s.id', 's.title', 's.createdAt', 's.updatedAt', 'a.username as author', 's.isEnabled as published', 's.isTrashed as trashed')
            ->where('s.validated = :validated')
            ->leftJoin('s.Author', 'a')
            ->setParameter('validated', false)
        ;

        return $qb->getQuery()
            ->getArrayResult()
        ;
    }

    public function getArrayById($id)
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s.id', 's.title', 's.createdAt', 's.updatedAt', 'a.username as author', 's.isEnabled as published', 's.isTrashed as trashed', 's.body as body')
            ->leftJoin('s.Author', 'a')
            ->where('s.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_ARRAY)
            ;
    }

    public function getByOpinion(Opinion $opinion, $offset, $limit, $filter, $trashed = false)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('ca', 'o', 'aut', 'm', 'media')
            ->leftJoin('s.Category', 'ca')
            ->leftJoin('s.Media', 'media')
            ->leftJoin('s.Opinion', 'o')
            ->leftJoin('s.Author', 'aut')
            ->leftJoin('aut.Media', 'm')
            ->andWhere('s.isTrashed = :trashed')
            ->andWhere('s.Opinion = :opinion')
            ->setParameter('opinion', $opinion)
            ->setParameter('trashed', $trashed)
        ;

        if ($filter === 'old') {
            $qb->addOrderBy('s.updatedAt', 'ASC');
        }

        if ($filter === 'last') {
            $qb->addOrderBy('s.updatedAt', 'DESC');
        }

        if ($filter === 'popular') {
            $qb->addOrderBy('s.voteCount', 'DESC');
            $qb->addOrderBy('s.updatedAt', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

    public function getByOpinionVersion(OpinionVersion $version, $offset, $limit, $filter, $trashed = false)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('ca', 'o', 'aut', 'm', 'media')
            ->leftJoin('s.Category', 'ca')
            ->leftJoin('s.Media', 'media')
            ->leftJoin('s.Opinion', 'o')
            ->leftJoin('s.Author', 'aut')
            ->leftJoin('aut.Media', 'm')
            ->andWhere('s.isTrashed = :trashed')
            ->andWhere('s.opinionVersion = :version')
            ->setParameter('version', $version)
            ->setParameter('trashed', $trashed)
        ;

        if ($filter === 'old') {
            $qb->addOrderBy('s.updatedAt', 'ASC');
        }

        if ($filter === 'last') {
            $qb->addOrderBy('s.updatedAt', 'DESC');
        }

        if ($filter === 'popular') {
            $qb->addOrderBy('s.voteCount', 'DESC');
            $qb->addOrderBy('s.updatedAt', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get one source by slug.
     *
     * @param $source
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOneBySlug($source)
    {
        return $this->getIsEnabledQueryBuilder()
            ->addSelect('a', 'm', 'v', 'o', 'cat', 'media')
            ->leftJoin('s.Author', 'a')
            ->leftJoin('s.Media', 'media')
            ->leftJoin('s.Category', 'cat')
            ->leftJoin('a.Media', 'm')
            ->leftJoin('s.votes', 'v')
            ->leftJoin('s.Opinion', 'o')
            ->andWhere('s.slug = :source')
            ->setParameter('source', $source)

            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get all trashed or unpublished sources for project.
     *
     * @param $step
     *
     * @return mixed
     */
    public function getTrashedOrUnpublishedByProject($project)
    {
        $qb = $this->createQueryBuilder('s')
            ->addSelect('ca', 'o', 'aut', 'm', 'media')
            ->leftJoin('s.Category', 'ca')
            ->leftJoin('s.Media', 'media')
            ->leftJoin('s.Author', 'aut')
            ->leftJoin('aut.Media', 'm')
            ->leftJoin('s.Opinion', 'o')
            ->leftJoin('o.step', 'step')
            ->leftJoin('step.projectAbstractStep', 'cas')
            ->andWhere('cas.project = :project')
            ->andWhere('s.isTrashed = :trashed')
            ->orWhere('s.isEnabled = :disabled')
            ->setParameter('project', $project)
            ->setParameter('trashed', true)
            ->setParameter('disabled', false)
            ->orderBy('s.trashedAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Get sources by opinion with user reports.
     *
     * @param $opinion
     * @param $user
     *
     * @return mixed
     */
    public function getByOpinionJoinUserReports($opinion, $user = null)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('ca', 'o', 'aut', 'm', 'media', 'r')
            ->leftJoin('s.Category', 'ca')
            ->leftJoin('s.Media', 'media')
            ->leftJoin('s.Opinion', 'o')
            ->leftJoin('s.Author', 'aut')
            ->leftJoin('aut.Media', 'm')
            ->leftJoin('s.Reports', 'r', 'WITH', 'r.Reporter =  :user')
            ->andWhere('s.isTrashed = :notTrashed')
            ->andWhere('s.Opinion = :opinion')
            ->setParameter('notTrashed', false)
            ->setParameter('opinion', $opinion)
            ->setParameter('user', $user)
            ->orderBy('s.updatedAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Get enabled sources by project step.
     *
     * @param $step
     *
     * @return mixed
     */
    public function getEnabledByConsultationStep(ConsultationStep $step)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('ca', 'o', 'ot', 'aut')
            ->leftJoin('s.Category', 'ca')
            ->leftJoin('s.Opinion', 'o')
            ->leftJoin('o.OpinionType', 'ot')
            ->leftJoin('s.Author', 'aut')
            ->andWhere('o.isEnabled = :oEnabled')
            ->setParameter('oEnabled', true)
            ->andWhere('o.step = :step')
            ->setParameter('step', $step)
            ->orderBy('s.updatedAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Get sources by user.
     *
     * @param $user
     *
     * @return mixed
     */
    public function getByUser($user)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('ca', 'o', 'cs', 'cas', 'c', 'aut', 'm', 'media')
            ->leftJoin('s.Category', 'ca')
            ->leftJoin('s.Media', 'media')
            ->leftJoin('s.Opinion', 'o')
            ->leftJoin('o.step', 'cs')
            ->leftJoin('cs.projectAbstractStep', 'cas')
            ->leftJoin('cas.project', 'c')
            ->leftJoin('s.Author', 'aut')
            ->leftJoin('aut.Media', 'm')
            ->andWhere('s.Author = :author')
            ->andWhere('o.isEnabled = :enabled')
            ->andWhere('cs.isEnabled = :enabled')
            ->andWhere('c.isEnabled = :enabled')
            ->setParameter('author', $user)
            ->setParameter('enabled', true)
            ->orderBy('s.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Count by user.
     *
     * @param $user
     *
     * @return mixed
     */
    public function countByUser($user)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->select('COUNT(s) as TotalSources')
            ->leftJoin('s.Opinion', 'o')
            ->leftJoin('o.step', 'cs')
            ->leftJoin('cs.projectAbstractStep', 'cas')
            ->leftJoin('cas.project', 'c')
            ->andWhere('o.isEnabled = :enabled')
            ->andWhere('cs.isEnabled = :enabled')
            ->andWhere('c.isEnabled = :enabled')
            ->andWhere('s.Author = :author')
            ->setParameter('enabled', true)
            ->setParameter('author', $user);

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    protected function getIsEnabledQueryBuilder()
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isEnabled = :isEnabled')
            ->setParameter('isEnabled', true);
    }
}
