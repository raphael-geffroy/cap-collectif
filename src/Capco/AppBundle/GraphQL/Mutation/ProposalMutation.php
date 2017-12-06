<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\Responses\AbstractResponse;
use Capco\AppBundle\Entity\Responses\MediaResponse;
use Capco\AppBundle\Entity\Selection;
use Capco\AppBundle\Entity\Steps\SelectionStep;
use Capco\AppBundle\Entity\Questions\MediaQuestion;
use Capco\AppBundle\Form\ProposalAdminType;
use Capco\AppBundle\Form\ProposalNotationType;
use Capco\AppBundle\Form\ProposalProgressStepType;
use Capco\UserBundle\Entity\User;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\ArrayCollection;

class ProposalMutation implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function delete(string $proposalId): array
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $proposal = $this->container->get('capco.proposal.repository')->find($proposalId);
        if (!$proposal) {
            throw new UserError(sprintf('Unknown proposal with id "%s"', $proposalId));
        }
        $em->remove($proposal);
        $em->flush();

        return ['proposal' => $proposal];
    }

    public function changeNotation(Argument $input)
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $formFactory = $this->container->get('form.factory');

        $values = $input->getRawArguments();
        $proposal = $this->container->get('capco.proposal.repository')->find($values['proposalId']);
        unset($values['proposalId']); // This only usefull to retrieve the proposal

        $form = $formFactory->create(ProposalNotationType::class, $proposal);
        $form->submit($values);

        if (!$form->isValid()) {
            throw new UserError('Input not valid : ' . (string) $form->getErrors(true, false));
        }

        $em->flush();

        return ['proposal' => $proposal];
    }

    public function changeProgressSteps(Argument $input): array
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $formFactory = $this->container->get('form.factory');

        $values = $input->getRawArguments();
        $proposal = $this->container->get('capco.proposal.repository')->find($values['proposalId']);
        if (!$proposal) {
            throw new UserError(sprintf('Unknown proposal with id "%s"', $values['proposalId']));
        }
        unset($values['proposalId']); // This only usefull to retrieve the proposal

        $form = $formFactory->create(ProposalProgressStepType::class, $proposal);
        $form->submit($values);

        if (!$form->isValid()) {
            throw new UserError('Input not valid : ' . (string) $form->getErrors(true, false));
        }

        $em->flush();

        return ['proposal' => $proposal];
    }

    public function changeCollectStatus(string $proposalId, string $statusId = null): array
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $proposal = $this->container->get('capco.proposal.repository')->find($proposalId);
        if (!$proposal) {
            throw new UserError('Cant find the proposal');
        }

        $status = null;
        if ($statusId) {
            $status = $this->container->get('capco.status.repository')->find($statusId);
        }

        $proposal->setStatus($status);
        $em->flush();

        $this->container->get('capco.notify_manager')->notifyProposalStatusChangeInCollect($proposal);

        return ['proposal' => $proposal];
    }

    public function changeSelectionStatus(string $proposalId, string $stepId, string $statusId = null): array
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $selection = $this->container->get('capco.selection.repository')->findOneBy([
            'proposal' => $proposalId,
            'selectionStep' => $stepId,
        ]);

        if (!$selection) {
            throw new UserError('Cant find the selection');
        }

        $status = null;
        if ($statusId) {
            $status = $this->container->get('capco.status.repository')->find($statusId);
        }

        $selection->setStatus($status);
        $em->flush();

        $this->container->get('capco.notify_manager')->notifyProposalStatusChangeInSelection($selection);

        $proposal = $this->container->get('capco.proposal.repository')->find($proposalId);

        return ['proposal' => $proposal];
    }

    public function unselectProposal(string $proposalId, string $stepId): array
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $selection = $this->container->get('capco.selection.repository')
            ->findOneBy(['proposal' => $proposalId, 'selectionStep' => $stepId]);

        if (!$selection) {
            throw new UserError('Cant find the selection');
        }
        $em->remove($selection);
        $em->flush();

        $proposal = $this->container->get('capco.proposal.repository')->find($proposalId);

        return ['proposal' => $proposal];
    }

    public function selectProposal(string $proposalId, string $stepId, string $statusId = null): array
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $selection = $this->container->get('capco.selection.repository')
            ->findOneBy(['proposal' => $proposalId, 'selectionStep' => $stepId]);
        if ($selection) {
            throw new UserError('Already selected');
        }

        $selectionStatus = null;

        if ($statusId) {
            $selectionStatus = $this->container->get('capco.status.repository')
                ->find($statusId);
        }

        $proposal = $this->container->get('capco.proposal.repository')->find($proposalId);
        $step = $this->container->get('capco.selection_step.repository')->find($stepId);

        $selection = new Selection();
        $selection->setSelectionStep($step);
        $selection->setStatus($selectionStatus);
        $proposal->addSelection($selection);

        $em->persist($selection);
        $em->flush();

        return ['proposal' => $proposal];
    }

    public function changePublicationStatus(Argument $values, User $user): array
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        if ($user && $user->isSuperAdmin()) {
            // If user is an admin, we allow to retrieve deleted proposal
            $em->getFilters()->disable('softdeleted');
        }
        /** @var Proposal $proposal */
        $proposal = $this->container->get('capco.proposal.repository')->find($values['proposalId']);
        if (!$proposal) {
            throw new UserError(sprintf('Unknown proposal with id "%s"', $values['proposalId']));
        }

        switch ($values['publicationStatus']) {
            case 'TRASHED':
                $proposal
                    ->setExpired(false)
                    ->setEnabled(true)
                    ->setDraft(false)
                    ->setTrashed(true)
                    ->setTrashedReason($values['trashedReason'])
                    ->setDeletedAt(null);
                break;
            case 'PUBLISHED':
                $proposal
                    ->setExpired(false)
                    ->setEnabled(true)
                    ->setDraft(false)
                    ->setTrashed(false)
                    ->setDeletedAt(null);
                break;
            case 'TRASHED_NOT_VISIBLE':
                $proposal
                    ->setExpired(false)
                    ->setEnabled(false)
                    ->setTrashed(true)
                    ->setDraft(false)
                    ->setTrashedReason($values['trashedReason'])
                    ->setDeletedAt(null);
                break;
            case 'DRAFT':
                $proposal
                    ->setDraft(true)
                    ->setExpired(false)
                    ->setEnabled(false)
                    ->setTrashed(false)
                    ->setDeletedAt(null);
                break;
            default:
                break;
        }

        $em->flush();

        return ['proposal' => $proposal];
    }

    public function changeContent(Argument $input, Request $request, User $user): array
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $logger = $this->container->get('logger');
        $formFactory = $this->container->get('form.factory');
        $questionRepo = $this->container->get('capco.abstract_question.repository');
        $proposalRepo = $this->container->get('capco.proposal.repository');

        $values = $input->getRawArguments();

        $proposal = $proposalRepo->find($values['id']);
        if (!$proposal) {
            $error = sprintf('Unknown proposal with id "%s"', $values['id']);
            $logger->error($error);
            throw new UserError($error);
        }

        unset($values['id']); // This only usefull to retrieve the proposal

        foreach ($values['responses'] as &$response) {
          $question = $questionRepo->find((int) $response['question']);
          if (!$question) {
              throw new UserError(sprintf('Unknown question with id "%d"', (int) $questionId));
          }
          $response['question'] = (int) $response['question'];
          if ($question instanceof MediaQuestion) {
              $response[AbstractResponse::TYPE_FIELD_NAME] = 'media_response';
          } else {
              $response[AbstractResponse::TYPE_FIELD_NAME] = 'value_response';
          }
        }

        $proposal->setResponses(new ArrayCollection());
        $form = $formFactory->create(ProposalAdminType::class, $proposal, [
            'proposalForm' => $proposal->getProposalForm(),
        ]);

        if (!$user->isSuperAdmin()) {
            if (isset($values['author'])) {
                $error = 'Only a user with role ROLE_SUPER_ADMIN can update an author.';
                $logger->error($error);
                // For now we only log an error and unset the subbmitted value…
                unset($values['author']);
            }
            $form->remove('author');
        }

        $logger->info('changeContent:' . json_encode($values, true));
        $form->submit($values);

        if (!$form->isValid()) {
            $error = 'Input not valid : ' . (string) $form->getErrors(true, false);
            $logger->error($error);
            throw new UserError($error);
        }

        $proposal->setUpdateAuthor($user);
        $em->flush();

        return ['proposal' => $proposal];
    }
}
