<?php

namespace Capco\AppBundle\Repository;

use Capco\AppBundle\Entity\Questions\MultipleChoiceQuestion;
use Doctrine\ORM\EntityRepository;

/**
 * QuestionChoiceRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class QuestionChoiceRepository extends EntityRepository
{
    public function hydrateFromIds(array $ids): array
    {
        $qb = $this->createQueryBuilder('qc');
        $qb->where('qc.id IN (:ids)')->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }

    public function findOneByQuestionAndTitle(MultipleChoiceQuestion $question, string $title)
    {
        $qb = $this->createQueryBuilder('qc')
            ->join('qc.question', 'q')
            ->where('q = :question')
            ->andWhere('qc.title = :title')
            ->setParameter('question', $question)
            ->setParameter('title', $title)
        ;

        return $qb->getQuery()->getSingleResult();
    }

}
