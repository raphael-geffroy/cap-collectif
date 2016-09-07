<?php

namespace Capco\AppBundle\Resolver;

use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\ProposalCollectVote;
use Capco\AppBundle\Entity\ProposalSelectionVote;
use Capco\AppBundle\Entity\Steps\CollectStep;
use Capco\AppBundle\Entity\Steps\SelectionStep;
use Capco\AppBundle\Repository\CollectStepRepository;
use Capco\AppBundle\Repository\ProposalCollectVoteRepository;
use Capco\AppBundle\Repository\ProposalSelectionVoteRepository;
use Capco\AppBundle\Repository\SelectionStepRepository;
use Capco\UserBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\Common\Collections\ArrayCollection;

class ProposalStepVotesResolver
{
    protected $proposalSelectionVoteRepository;
    protected $selectionStepRepository;
    protected $proposalCollectVoteRepository;
    protected $collectStepRepository;

    public function __construct(
      ProposalSelectionVoteRepository $proposalSelectionVoteRepository,
      SelectionStepRepository $selectionStepRepository,
      ProposalCollectVoteRepository $proposalCollectVoteRepository,
      CollectStepRepository $collectStepRepository
      )
    {
        $this->proposalSelectionVoteRepository = $proposalSelectionVoteRepository;
        $this->selectionStepRepository = $selectionStepRepository;
        $this->proposalCollectVoteRepository = $proposalCollectVoteRepository;
        $this->collectStepRepository = $collectStepRepository;
    }

    public function addVotesToProposalsForSelectionStepAndUser(array $proposals, SelectionStep $selectionStep, User $user)
    {
        $usersVotesForSelectionStep = $this
            ->proposalSelectionVoteRepository
            ->findBy(
                [
                    'selectionStep' => $selectionStep,
                    'user' => $user,
                ]
            );
        $results = [];
        foreach ($proposals as $proposal) {
            $proposal['userHasVote'] = $this->proposalHasVote($proposal, $usersVotesForSelectionStep);
            $results[] = $proposal;
        }

        return $results;
    }

    public function addVotesToProposalsForCollectStepAndUser(array $proposals, CollectStep $collectStep, User $user)
    {
        $usersVotesForCollectStep = $this
            ->proposalCollectVoteRepository
            ->findBy(
                [
                    'collectStep' => $collectStep,
                    'user' => $user,
                ]
            );
        $results = [];
        foreach ($proposals as $proposal) {
            $proposal['userHasVote'] = $this->proposalHasVote($proposal, $usersVotesForCollectStep);
            $results[] = $proposal;
        }

        return $results;
    }

    public function proposalHasVote($proposal, $usersVotesForStep)
    {
        foreach ($usersVotesForStep as $vote) {
            if ($vote->getProposal()->getId() === $proposal['id']) {
                return true;
            }
        }

        return false;
    }

    private function checkIntanceOfProposalVote($vote)
    {
        switch (true) {
            case $vote instanceof ProposalSelectionVote:
                $step = $vote->getSelectionStep();
                $connected = $this->proposalSelectionVoteRepository->findBy(['selectionStep' => $step, 'user' => $vote->getUser()]);
                $anonymous = $this->proposalSelectionVoteRepository->findBy(['selectionStep' => $step, 'email' => $vote->getEmail()]);
                break;
            case $vote instanceof ProposalCollectVote:
                $step = $vote->getCollectStep();
                $connected = $this->proposalCollectVoteRepository->findBy(['collectStep' => $step, 'user' => $vote->getUser()]);
                $anonymous = $this->proposalCollectVoteRepository->findBy(['collectStep' => $step, 'email' => $vote->getEmail()]);
                break;
            default:
                throw new NotFoundHttpException();
        }

        $proposal = $vote->getProposal();
        $otherVotes = [];
        if ($vote->getUser()) {
            $otherVotes = $connected;
        } elseif ($vote->getEmail()) {
            $otherVotes = $anonymous;
        }

        if (!$step->isVotable()) {
            return false;
        }

        if ($vote instanceof ProposalSelectionVote) {
            if ($step->isBudgetVotable() && $step->getBudget()) {
                $left = $step->getBudget() - $this->getAmountSpentForVotes($otherVotes);

                return $left >= $proposal->getEstimation();
            }
        }

        return true;
    }

    public function voteIsPossible($vote)
    {
        return $this->checkIntanceOfProposalVote($vote);
    }

    public function getAmountSpentForVotes(array $votes)
    {
        $spent = 0;
        foreach ($votes as $vote) {
            $spent += $vote->getProposal()->getEstimation();
        }

        return $spent;
    }

    public function getSpentCreditsForUser(User $user, SelectionStep $selectionStep)
    {
        $votes = $this
            ->proposalSelectionVoteRepository
            ->findBy(
                [
                    'selectionStep' => $selectionStep,
                    'user' => $user,
                ]
            )
        ;

        return $this->getAmountSpentForVotes($votes);
    }

    public function getCreditsLeftForUser(User $user = null, SelectionStep $selectionStep)
    {
        $creditsLeft = $selectionStep->getBudget();
        if ($creditsLeft > 0 && $user && $selectionStep->isBudgetVotable()) {
            $creditsLeft -= $this
                ->getSpentCreditsForUser($user, $selectionStep)
            ;
        }

        return $creditsLeft;
    }

    public function getVotableStepsForProposal(Proposal $proposal)
    {
        $votableSteps = new ArrayCollection();
        $collectStep = $proposal->getProposalForm()->getStep();
        if ($collectStep->isVotable()) {
            $votableSteps->add($collectStep);
        }
        $votableSteps->concat(
          $this
            ->selectionStepRepository
            ->getVotableStepsForProposal($proposal)
        );
        return $votableSteps;
    }

    public function getVotableStepsForProject(Project $project)
    {
        return $this
            ->selectionStepRepository
            ->getVotableStepsForProject($project)
        ;
    }

    public function getVotableStepsNotFutureForProject(Project $project)
    {
        $steps = [];
        foreach ($this->getVotableStepsForProject($project) as $step) {
            if (!$step->isFuture()) {
                $steps[] = $step;
            }
        }

        return $steps;
    }

    public function getFirstVotableStepForProposal(Proposal $proposal)
    {
        $votableSteps = $this->getVotableStepsForProposal($proposal);
        $firstVotableStep = null;
        foreach ($votableSteps as $step) {
            if ($step->isOpen()) {
                $firstVotableStep = $step;
                break;
            }
        }
        if (!$firstVotableStep) {
            foreach ($votableSteps as $step) {
                if ($step->isFuture()) {
                    $firstVotableStep = $step;
                    break;
                }
            }
        }
        if (!$firstVotableStep) {
            foreach ($votableSteps as $step) {
                if ($step->isClosed()) {
                    $firstVotableStep = $step;
                    break;
                }
            }
        }

        return $firstVotableStep;
    }

    public function hasVotableStepNotFuture(Project $project)
    {
        return count($this->getVotableStepsNotFutureForProject($project)) > 0;
    }

    public function getVotesCountForUserInSelectionStep(User $user, SelectionStep $step)
    {
        return $this
            ->proposalSelectionVoteRepository
            ->countForUserAndStep($user, $step)
        ;
    }

    public function getVotesForUserInSelectionStep(User $user, SelectionStep $step)
    {
        return $this
            ->proposalSelectionVoteRepository
            ->InCollectStep($user, $step)
        ;
    }
}
