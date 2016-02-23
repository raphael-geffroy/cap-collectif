<?php

namespace Capco\AppBundle\Resolver;

use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Entity\Steps\AbstractStep;
use Capco\AppBundle\Entity\Steps\CollectStep;
use Capco\AppBundle\Entity\Steps\ConsultationStep;
use Capco\AppBundle\Entity\Steps\SelectionStep;
use Capco\UserBundle\Repository\UserRepository;
use GuzzleHttp\Stream\Exception\SeekException;

class ContributionResolver
{
    protected $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    // Code may looks ugly but in fact it's highly optimized !
    public function getProjectContributorsOrdered(Project $project, $pagination = 16, $page = 1)
    {
        // Fetch contributors
        $sourcesContributors = $this->repository->findProjectSourceContributorsWithCount($project);
        $argumentsContributors = $this->repository->findProjectArgumentContributorsWithCount($project);
        $opinionsContributors = $this->repository->findProjectOpinionContributorsWithCount($project);
        $proposalsContributors = $this->repository->findProjectProposalContributorsWithCount($project);
        $versionsContributors = $this->repository->findProjectVersionContributorsWithCount($project);

        // Fetch voters
        $opinionsVoters = $this->repository->findProjectOpinionVotersWithCount($project);
        $versionsVoters = $this->repository->findProjectVersionVotersWithCount($project);
        $argumentsVoters = $this->repository->findProjectArgumentVotersWithCount($project);
        $sourcesVoters = $this->repository->findProjectSourceVotersWithCount($project);
        $proposalsVoters = $this->repository->findProjectProposalVotersWithCount($project);

        $contributors = [];

        foreach ($sourcesContributors as $sourcesContributor) {
            $contributors[$sourcesContributor['id']]['sources'] = $sourcesContributor['sources_count'];
        }

        foreach ($argumentsContributors as $argumentsContributor) {
            $contributors[$argumentsContributor['id']]['arguments'] = $argumentsContributor['arguments_count'];
        }

        foreach ($opinionsContributors as $opinionsContributor) {
            $contributors[$opinionsContributor['id']]['opinions'] = $opinionsContributor['opinions_count'];
        }

        foreach ($proposalsContributors as $proposalsContributor) {
            $contributors[$proposalsContributor['id']]['proposals'] = $proposalsContributor['proposals_count'];
        }

        foreach ($versionsContributors as $versionContributor) {
            $contributors[$versionContributor['id']]['versions'] = $versionContributor['versions_count'];
        }

        foreach ($opinionsVoters as $opinionsVoter) {
            $contributors[$opinionsVoter['id']]['opinions_votes'] = $opinionsVoter['opinions_votes_count'];
        }

        foreach ($versionsVoters as $versionVoter) {
            $contributors[$versionVoter['id']]['versions_votes'] = $versionVoter['versions_votes_count'];
        }

        foreach ($argumentsVoters as $argumentsVoter) {
            $contributors[$argumentsVoter['id']]['arguments_votes'] = $argumentsVoter['arguments_votes_count'];
        }

        foreach ($sourcesVoters as $sourcesVoter) {
            $contributors[$sourcesVoter['id']]['sources_votes'] = $sourcesVoter['sources_votes_count'];
        }

        foreach ($proposalsVoters as $proposalsVoter) {
            $contributors[$proposalsVoter['id']]['proposals_votes'] = $proposalsVoter['proposals_votes_count'];
        }

        foreach ($contributors as &$contributor) {
            $contributor['total'] = isset($contributor['sources']) ? $contributor['sources'] : 0;
            $contributor['total'] += isset($contributor['arguments']) ? $contributor['arguments'] : 0;
            $contributor['total'] += isset($contributor['opinions']) ? $contributor['opinions'] : 0;
            $contributor['total'] += isset($contributor['proposals']) ? $contributor['proposals'] : 0;
            $contributor['total'] += isset($contributor['versions']) ? $contributor['versions'] : 0;
            $contributor['total'] += isset($contributor['opinions_votes']) ? $contributor['opinions_votes'] : 0;
            $contributor['total'] += isset($contributor['versions_votes']) ? $contributor['versions_votes'] : 0;
            $contributor['total'] += isset($contributor['arguments_votes']) ? $contributor['arguments_votes'] : 0;
            $contributor['total'] += isset($contributor['sources_votes']) ? $contributor['sources_votes'] : 0;
            $contributor['total'] += isset($contributor['proposals_votes']) ? $contributor['proposals_votes'] : 0;
        }

        uasort($contributors, function ($a, $b) { return $b['total'] - $a['total']; });

        if ($pagination && $page) {
            $contributorsPage = array_slice($contributors, $pagination * $page - $pagination, $pagination, true);

            $users = $this->repository->findWithMediaByIds(array_keys($contributorsPage));

            foreach ($users as $user) {
                $contributors[$user->getId()]['user'] = $user;
            }
        }

        return $contributors;
    }

    public function countProjectContributors(Project $project)
    {
        return count($this->getProjectContributorsOrdered($project));
    }

    // Code may looks ugly but in fact it's highly optimized !
    public function getStepContributorsOrdered(AbstractStep $step)
    {
        $contributors = [];

        if ($step instanceof ConsultationStep) {
            // Fetch contributors
            $sourcesContributors = $this->repository->findConsultationStepSourceContributorsWithCount($step);
            $argumentsContributors = $this->repository->findConsultationStepArgumentContributorsWithCount($step);
            $opinionsContributors = $this->repository->findConsultationStepOpinionContributorsWithCount($step);
            $versionsContributors = $this->repository->findConsultationStepVersionContributorsWithCount($step);
            // Fetch voters
            $opinionsVoters = $this->repository->findConsultationStepOpinionVotersWithCount($step);
            $versionsVoters = $this->repository->findConsultationStepVersionVotersWithCount($step);
            $argumentsVoters = $this->repository->findConsultationStepArgumentVotersWithCount($step);
            $sourcesVoters = $this->repository->findConsultationStepSourceVotersWithCount($step);
            // Fill array
            foreach ($sourcesContributors as $sourcesContributor) {
                $contributors[$sourcesContributor['id']]['sources'] = $sourcesContributor['sources_count'];
            }
            foreach ($argumentsContributors as $argumentsContributor) {
                $contributors[$argumentsContributor['id']]['arguments'] = $argumentsContributor['arguments_count'];
            }
            foreach ($opinionsContributors as $opinionsContributor) {
                $contributors[$opinionsContributor['id']]['opinions'] = $opinionsContributor['opinions_count'];
            }
            foreach ($versionsContributors as $versionContributor) {
                $contributors[$versionContributor['id']]['versions'] = $versionContributor['versions_count'];
            }
            foreach ($opinionsVoters as $opinionsVoter) {
                $contributors[$opinionsVoter['id']]['opinions_votes'] = $opinionsVoter['opinions_votes_count'];
            }
            foreach ($versionsVoters as $versionVoter) {
                $contributors[$versionVoter['id']]['versions_votes'] = $versionVoter['versions_votes_count'];
            }
            foreach ($argumentsVoters as $argumentsVoter) {
                $contributors[$argumentsVoter['id']]['arguments_votes'] = $argumentsVoter['arguments_votes_count'];
            }
            foreach ($sourcesVoters as $sourcesVoter) {
                $contributors[$sourcesVoter['id']]['sources_votes'] = $sourcesVoter['sources_votes_count'];
            }
        } else if ($step instanceof CollectStep) {
            $proposalsContributors = $this->repository->findCollectStepProposalContributorsWithCount($step);
            foreach ($proposalsContributors as $proposalsContributor) {
                $contributors[$proposalsContributor['id']]['proposals'] = $proposalsContributor['proposals_count'];
            }
        } else if ($step instanceof SelectionStep) {
            $proposalsVoters = $this->repository->findSelectionStepProposalVotersWithCount($step);
            foreach ($proposalsVoters as $proposalsVoter) {
                $contributors[$proposalsVoter['id']]['proposals_votes'] = $proposalsVoter['proposals_votes_count'];
            }
        }

        foreach ($contributors as &$contributor) {
            $contributor['total'] = isset($contributor['sources']) ? $contributor['sources'] : 0;
            $contributor['total'] += isset($contributor['arguments']) ? $contributor['arguments'] : 0;
            $contributor['total'] += isset($contributor['opinions']) ? $contributor['opinions'] : 0;
            $contributor['total'] += isset($contributor['proposals']) ? $contributor['proposals'] : 0;
            $contributor['total'] += isset($contributor['versions']) ? $contributor['versions'] : 0;
            $contributor['total'] += isset($contributor['opinions_votes']) ? $contributor['opinions_votes'] : 0;
            $contributor['total'] += isset($contributor['versions_votes']) ? $contributor['versions_votes'] : 0;
            $contributor['total'] += isset($contributor['arguments_votes']) ? $contributor['arguments_votes'] : 0;
            $contributor['total'] += isset($contributor['sources_votes']) ? $contributor['sources_votes'] : 0;
            $contributor['total'] += isset($contributor['proposals_votes']) ? $contributor['proposals_votes'] : 0;
        }

        uasort($contributors, function ($a, $b) { return $b['total'] - $a['total']; });

        return $contributors;
    }

    public function countStepContributors(AbstractStep $step)
    {
        return count($this->getStepContributorsOrdered($step));
    }

    public function countProjectContributions(Project $project)
    {
        $count = 0;
        foreach ($project->getSteps() as $step) {
            if ($step->getStep()->isConsultationStep()) {
                $count += $step->getStep()->getContributionsCount();
            }
            if ($step->getStep()->isCollectStep()) {
                $count += $step->getStep()->getProposalsCount();
            }
        }

        return $count;
    }

    public function countStepVotes(AbstractStep $step)
    {
        $count = 0;
        if ($step instanceof ConsultationStep) {
            foreach ($step->getOpinions() as $opinion) {
                $count += $opinion->getVotesCountAll();
                foreach ($opinion->getArguments() as $argument) {
                    $count += $argument->getVotesCount();
                }
                foreach ($opinion->getSources() as $source) {
                    $count += $source->getVotesCount();
                }
                foreach ($opinion->getVersions() as $version) {
                    $count += $version->getVotesCountAll();
                    foreach ($version->getArguments() as $argument) {
                        $count += $argument->getVotesCount();
                    }
                    foreach ($version->getSources() as $source) {
                        $count += $source->getVotesCount();
                    }
                }
            }
        } else if ($step instanceof SelectionStep) {
            $count += $step->getVotesCount();
        }

        return $count;
    }

    public function countProjectVotes(Project $project)
    {
        $count = 0;
        foreach ($project->getSteps() as $pas) {
            $count += $this->countStepVotes($pas->getStep());
        }
        return $count;
    }
}
