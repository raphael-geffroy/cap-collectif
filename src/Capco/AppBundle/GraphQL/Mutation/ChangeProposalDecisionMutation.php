<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\Entity\Post;
use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\ProposalDecision;
use Capco\AppBundle\Entity\Status;
use Capco\AppBundle\Enum\ProposalAssessmentState;
use Capco\AppBundle\GraphQL\Resolver\Traits\ResolverTrait;
use Capco\AppBundle\Repository\PostRepository;
use Capco\AppBundle\Repository\ProposalRepository;
use Capco\AppBundle\Repository\StatusRepository;
use Capco\AppBundle\Security\ProposalAnalysisRelatedVoter;
use Capco\UserBundle\Entity\User;
use Capco\UserBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Relay\Node\GlobalId;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Translation\TranslatorInterface;

class ChangeProposalDecisionMutation implements MutationInterface
{
    use ResolverTrait;

    private $proposalRepository;
    private $entityManager;
    private $authorizationChecker;
    private $logger;
    private $proposalDecisionRepository;
    private $userRepository;
    private $postRepository;
    private $translator;
    private $statusRepository;

    public function __construct(
        ProposalRepository $proposalRepository,
        UserRepository $userRepository,
        PostRepository $postRepository,
        EntityManagerInterface $entityManager,
        StatusRepository $statusRepository,
        AuthorizationChecker $authorizationChecker,
        LoggerInterface $logger,
        TranslatorInterface $translator
    ) {
        $this->proposalRepository = $proposalRepository;
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->logger = $logger;
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->translator = $translator;
        $this->statusRepository = $statusRepository;
    }

    public function __invoke(Argument $args, $viewer): array
    {
        $this->preventNullableViewer($viewer);

        list($proposalId, $body, $estimatedCost, $authors, $decision, $refusedReason, $isDone) = [
            $args->offsetGet('proposalId'),
            $args->offsetGet('body'),
            $args->offsetGet('estimatedCost'),
            $args->offsetGet('authors') ?? [],
            $args->offsetGet('isApproved') ?? true,
            $args->offsetGet('refusedReason'),
            $args->offsetGet('isDone') ?? false
        ];

        $proposalId = GlobalId::fromGlobalId($proposalId)['id'];
        $proposal = $this->proposalRepository->find($proposalId);

        if (!$proposal) {
            return $this->buildPayload(null, 'The proposal does not exist.');
        }

        /** @var Proposal $proposal */
        if (
            !$this->authorizationChecker->isGranted(ProposalAnalysisRelatedVoter::DECIDE, $proposal)
        ) {
            return $this->buildPayload(
                null,
                'You can\'t give a decision on a proposal you are not assigned to.'
            );
        }

        if (false === $decision && !$refusedReason) {
            return $this->buildPayload(
                null,
                'The refused reason must not be empty if the proposal is refused.'
            );
        }

        // If there is no proposalDecision related to the given proposal, create it.
        if (!($proposalDecision = $proposal->getDecision())) {
            $proposalDecision = $this->createProposalDecision($proposal);
        }

        $proposalDecision
            ->setIsApproved($decision)
            ->setUpdatedBy($viewer)
            ->setEstimatedCost($estimatedCost)
            ->setIsDone($isDone);
        $post = $proposalDecision->getPost();
        $post->setBody($body);
        // Remove and add authors.
        $this->handlePostAuthors($post, $authors);

        if ($refusedReason) {
            /** @var Status $status */
            $status = $this->statusRepository->find($refusedReason);
            $proposalDecision->setRefusedReason($status);
        }

        // If the decision is given, change state of the assessment if its not already given.
        if (
            $isDone &&
            ($proposalAssessment = $proposal->getAssessment()) &&
            ProposalAssessmentState::IN_PROGRESS === $proposalAssessment->getState()
        ) {
            $proposalAssessment->setState(ProposalAssessmentState::TOO_LATE);
        }

        try {
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            $this->logger->alert(
                'An error occurred when editing Post and ProposalDecision with proposal id :' .
                    $proposalId .
                    '.' .
                    $exception->getMessage()
            );

            return $this->buildPayload(null, 'An error occurred when creating proposal decision.');
        }

        return $this->buildPayload($proposalDecision);
    }

    private function buildPayload(
        ?ProposalDecision $decision = null,
        ?string $errorMessage = null
    ): array {
        return [
            'decision' => $decision,
            'userErrors' => [['message' => $errorMessage]]
        ];
    }

    private function handlePostAuthors(Post $post, array $requestAuthors): void
    {
        $existingAuthors = array_map(static function (User $author) {
            return $author->getId();
        }, $post->getAuthors()->toArray());
        $authorIds = array_map(static function (string $authorGlobalId) {
            return GlobalId::fromGlobalId($authorGlobalId)['id'];
        }, $requestAuthors);
        $authorsToDelete = $this->userRepository->findBy([
            'id' => array_diff($existingAuthors, $authorIds)
        ]);
        foreach ($authorsToDelete as $authorToDelete) {
            // @var User $postAuthor
            $post->removeAuthor($authorToDelete);
            unset($existingAuthors[$authorToDelete->getId()]);
        }

        $authorsToAdd = $this->userRepository->findBy([
            'id' => array_diff($authorIds, $existingAuthors)
        ]);
        foreach ($authorsToAdd as $authorToAdd) {
            // @var User $user
            $post->addAuthor($authorToAdd);
        }
    }

    private function createProposalDecision(Proposal $proposal): ProposalDecision
    {
        $post = new Post();
        $proposalDecision = new ProposalDecision($proposal, $post);

        $post
            ->setTitle(
                $this->translator->trans(
                    'proposal_decision.official_response',
                    [],
                    'CapcoAppBundle'
                )
            )
            ->addProject($proposal->getProject())
            ->addProposal($proposal)
            ->setIsPublished(false)
            ->setdisplayedOnBlog(false);

        $proposalDecision->setProposal($proposal);

        $this->entityManager->persist($post);
        $this->entityManager->persist($proposalDecision);

        return $proposalDecision;
    }
}