<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\Elasticsearch\Indexer;
use Capco\AppBundle\Form\RegistrationFormQuestionsUpdateType;
use Capco\AppBundle\GraphQL\Exceptions\GraphQLException;
use Capco\AppBundle\GraphQL\Traits\QuestionPersisterTrait;
use Capco\AppBundle\Repository\MultipleChoiceQuestionRepository;
use Capco\AppBundle\Repository\QuestionnaireAbstractQuestionRepository;
use Capco\AppBundle\Repository\AbstractQuestionRepository;
use Capco\AppBundle\Repository\RegistrationFormRepository;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;

class UpdateRegistrationFormQuestionsMutation implements MutationInterface
{
    use QuestionPersisterTrait;

    private $em;
    private $formFactory;
    private $registrationFormRepository;
    private $logger;
    private $questionRepo;
    private $abstractQuestionRepo;
    private $choiceQuestionRepository;
    private $indexer;

    public function __construct(
        EntityManagerInterface $em,
        FormFactoryInterface $formFactory,
        RegistrationFormRepository $registrationFormRepository,
        LoggerInterface $logger,
        QuestionnaireAbstractQuestionRepository $questionRepo,
        AbstractQuestionRepository $abstractQuestionRepo,
        MultipleChoiceQuestionRepository $choiceQuestionRepository,
        Indexer $indexer
    ) {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->registrationFormRepository = $registrationFormRepository;
        $this->logger = $logger;
        $this->questionRepo = $questionRepo;
        $this->abstractQuestionRepo = $abstractQuestionRepo;
        $this->choiceQuestionRepository = $choiceQuestionRepository;
        $this->indexer = $indexer;
    }

    public function __invoke(Argument $input): array
    {
        $arguments = $input->getArrayCopy();

        $registrationForm = $this->registrationFormRepository->findCurrent();

        if (!$registrationForm) {
            throw new UserError('No registration form');
        }

        $form = $this->formFactory->create(
            RegistrationFormQuestionsUpdateType::class,
            $registrationForm
        );

        if (isset($arguments['questions'])) {
            $oldChoices = $this->getQuestionChoicesValues($registrationForm->getId());
            $this->handleQuestions($form, $registrationForm, $arguments, 'registration');
        } else {
            $form->submit($arguments, false);
        }

        if (!$form->isValid()) {
            $this->logger->error(__METHOD__ . (string) $form->getErrors(true, false));

            throw GraphQLException::fromFormErrors($form);
        }

        $this->em->flush();

        if (isset($oldChoices)) {
            // We index all the question choices synchronously to avoid a
            // difference between datas saved in db and in elasticsearch.
            $newChoices = $this->getQuestionChoicesValues($registrationForm->getId());
            $mergedChoices = array_unique(array_merge($oldChoices, $newChoices));
            $this->indexQuestionChoicesValues($mergedChoices);
        }

        return compact('registrationForm');
    }
}
