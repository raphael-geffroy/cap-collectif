<?php

namespace Capco\AppBundle\Repository;

use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Entity\Questionnaire;
use Capco\AppBundle\Entity\Steps\QuestionnaireStep;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class ReplyRepository extends EntityRepository
{
    public function countPublishedForQuestionnaire(Questionnaire $questionnaire)
    {
        $qb = $this
            ->getIsEnabledQueryBuilder()
            ->select('COUNT(reply.id) as repliesCount')
            ->leftJoin('reply.questionnaire', 'questionnaire')
            ->andWhere('questionnaire.id = :questionnaireId')
            ->setParameter('questionnaireId', $questionnaire->getId())
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getOneForUserAndQuestionnaire(Questionnaire $questionnaire, User $user)
    {
        $qb = $this
            ->getIsEnabledQueryBuilder()
            ->andWhere('reply.questionnaire = :questionnaire')
            ->andWhere('reply.author = :user')
            ->setParameter('questionnaire', $questionnaire)
            ->setParameter('user', $user)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getEnabledByQuestionnaireAsArray(Questionnaire $questionnaire)
    {
        $qb = $this->createQueryBuilder('reply')
            ->andWhere('reply.enabled = true')
            ->addSelect('author')
            ->leftJoin('reply.author', 'author')
            ->andWhere('reply.questionnaire = :questionnaire')
            ->setParameter('questionnaire', $questionnaire)
        ;

        return $qb->getQuery()->getArrayResult();
    }

    public function countByAuthorAndProject(User $author, Project $project): int
    {
        $qb = $this
          ->getIsEnabledQueryBuilder()
          ->select('COUNT(DISTINCT reply)')
          ->leftJoin('reply.questionnaire', 'questionnaire')
          ->andWhere('questionnaire.step IN (:steps)')
          ->andWhere('reply.author = :author')
          ->setParameter('steps', array_map(function ($step) {
              return $step;
          }, $project->getRealSteps()))
          ->setParameter('author', $author)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function countByAuthorAndStep(User $author, QuestionnaireStep $step): int
    {
        $qb = $this
          ->getIsEnabledQueryBuilder()
          ->select('COUNT(DISTINCT reply)')
          ->leftJoin('reply.questionnaire', 'questionnaire')
          ->andWhere('questionnaire.step = :step')
          ->andWhere('reply.author = :author')
          ->setParameter('step', $step)
          ->setParameter('author', $author)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    protected function getIsEnabledQueryBuilder()
    {
        return $this->createQueryBuilder('reply')
            ->andWhere('reply.enabled = true')
            ->andWhere('reply.expired = false')
          ;
    }
}
