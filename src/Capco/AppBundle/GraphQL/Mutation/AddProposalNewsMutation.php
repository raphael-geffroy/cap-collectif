<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\Entity\Post;
use Capco\AppBundle\Entity\PostTranslation;
use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\Steps\CollectStep;
use Capco\AppBundle\Entity\Steps\SelectionStep;
use Capco\AppBundle\Form\ProposalPostType;
use Capco\AppBundle\GraphQL\Exceptions\GraphQLException;
use Capco\AppBundle\GraphQL\Mutation\Locale\LocaleUtils;
use Capco\AppBundle\GraphQL\Resolver\GlobalIdResolver;
use Capco\AppBundle\Mailer\Message\AbstractMessage;
use Capco\AppBundle\Repository\LocaleRepository;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Argument as Arg;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AddProposalNewsMutation implements MutationInterface
{
    public const PROPOSAL_NOT_FOUND = 'PROPOSAL_NOT_FOUND';
    public const PROPOSAL_DOESNT_ALLOW_NEWS = 'PROPOSAL_DOESNT_ALLOW_NEWS';
    public const ACCESS_DENIED = 'ACCESS_DENIED';

    private EntityManagerInterface $em;
    private GlobalIdResolver $globalIdResolver;
    private FormFactoryInterface $formFactory;
    private LoggerInterface $logger;
    private TranslatorInterface $translator;
    private LocaleRepository $localeRepository;

    public function __construct(
        EntityManagerInterface $em,
        FormFactoryInterface $formFactory,
        GlobalIdResolver $globalIdResolver,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        LocaleRepository $localeRepository
    ) {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->globalIdResolver = $globalIdResolver;
        $this->logger = $logger;
        $this->translator = $translator;
        $this->localeRepository = $localeRepository;
    }

    public function __invoke(Arg $input, User $viewer): array
    {
        try {
            $proposal = $this->getProposal($input, $viewer);
            $this->checkProjectAllowProposalNews($proposal);
            $proposalPost = $this->createProposalPost($input, $proposal, $viewer);

            return ['proposalPost' => $proposalPost, 'errorCode' => null];
        } catch (UserError $error) {
            return ['errorCode' => $error->getMessage()];
        }
    }

    private function getProposal(Arg $input, User $viewer): Proposal
    {
        $proposalGlobalId = $input->offsetGet('proposalId');
        $proposal = $this->globalIdResolver->resolve($proposalGlobalId, $viewer);
        if (!$proposal || !$proposal instanceof Proposal) {
            $this->logger->error('Unknown proposal with id: ' . $proposalGlobalId);

            throw new UserError(self::PROPOSAL_NOT_FOUND);
        }
        if ($proposal->getAuthor() !== $viewer && !$viewer->isAdmin()) {
            throw new UserError(self::ACCESS_DENIED);
        }

        return $proposal;
    }

    private function checkProjectAllowProposalNews(Proposal $proposal): void
    {
        $step = $proposal->getProject()->getCurrentStep();
        if (
            !$step ||
            ($step &&
                (($step instanceof CollectStep || $step instanceof SelectionStep) &&
                    !$step->isAllowAuthorsToAddNews()))
        ) {
            throw new UserError(self::PROPOSAL_DOESNT_ALLOW_NEWS);
        }
    }

    private function createProposalPost(Arg $input, Proposal $proposal, User $viewer): Post
    {
        $proposalPost = new Post();
        $proposalPost->addProposal($proposal);
        $proposalPost->addAuthor($viewer);
        $proposalPost->setDisplayedOnBlog(false);
        $proposalPost->publishNow();

        $values = $input->getArrayCopy();
        unset($values['proposalId']);
        $form = $this->formFactory->create(ProposalPostType::class, $proposalPost);
        $form->submit($values, false);

        if (!$form->isValid()) {
            throw new UserError(UpdateProposalNewsMutation::INVALID_DATA);
        }

        LocaleUtils::indexTranslations($values);

        foreach ($this->localeRepository->findEnabledLocalesCodes() as $availableLocale) {
            if (isset($values['translations'][$availableLocale])) {
                $translation = new PostTranslation();
                $translation->setTranslatable($proposalPost);
                $translation->setLocale($availableLocale);
                if (isset($values['translations'][$availableLocale]['title'])) {
                    $translation->setTitle($values['translations'][$availableLocale]['title']);
                }
                if (isset($values['translations'][$availableLocale]['body'])) {
                    $translation->setBody(
                        AbstractMessage::escape($values['translations'][$availableLocale]['body'])
                    );
                }
                if (isset($values['translations'][$availableLocale]['abstract'])) {
                    $translation->setAbstract(
                        $values['translations'][$availableLocale]['abstract']
                    );
                }
                $proposalPost->addTranslation($translation);
            }
        }
        $this->em->persist($proposalPost);
        $this->em->flush();

        return $proposalPost;
    }
}
