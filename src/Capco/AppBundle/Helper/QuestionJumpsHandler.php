<?php

namespace Capco\AppBundle\Helper;

use Capco\AppBundle\Elasticsearch\Indexer;
use Capco\AppBundle\Entity\QuestionChoice;
use Capco\AppBundle\Entity\Questionnaire;
use Capco\AppBundle\Entity\Questions\AbstractQuestion;
use Capco\AppBundle\Form\QuestionnaireConfigurationUpdateType;
use Capco\AppBundle\GraphQL\Traits\QuestionPersisterTrait;
use Capco\AppBundle\Repository\AbstractQuestionRepository;
use Capco\AppBundle\Repository\MultipleChoiceQuestionRepository;
use Capco\AppBundle\Repository\QuestionChoiceRepository;
use Capco\AppBundle\Repository\QuestionnaireAbstractQuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Relay\Node\GlobalId;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class QuestionJumpsHandler
{
    use QuestionPersisterTrait;

    private QuestionChoiceRepository $questionChoiceRepository;
    private AbstractQuestionRepository $questionRepository;
    private FormFactoryInterface $formFactory;
    private EntityManagerInterface $em;
    private AbstractQuestionRepository $abstractQuestionRepo;
    private QuestionnaireAbstractQuestionRepository $questionRepo;
    private LoggerInterface $logger;
    private ValidatorInterface $colorValidator;
    private MultipleChoiceQuestionRepository $choiceQuestionRepository;
    private Indexer $indexer;

    public function __construct(
        QuestionChoiceRepository $questionChoiceRepository,
        AbstractQuestionRepository $questionRepository,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $em,
        AbstractQuestionRepository $abstractQuestionRepo,
        QuestionnaireAbstractQuestionRepository $questionRepo,
        LoggerInterface $logger,
        ValidatorInterface $colorValidator,
        MultipleChoiceQuestionRepository $choiceQuestionRepository,
        Indexer $indexer
    ) {
        $this->questionChoiceRepository = $questionChoiceRepository;
        $this->questionRepository = $questionRepository;
        $this->formFactory = $formFactory;
        $this->em = $em;
        $this->abstractQuestionRepo = $abstractQuestionRepo;
        $this->questionRepo = $questionRepo;
        $this->logger = $logger;
        $this->colorValidator = $colorValidator;
        $this->choiceQuestionRepository = $choiceQuestionRepository;
        $this->indexer = $indexer;
    }

    public function saveJumps($arguments, Questionnaire $questionnaire): void
    {
        $questions = $arguments['questions'] ?? null;

        if (!$questions) {
            return;
        }

        $this->parseTemporaryId($arguments['questions']);

        $this->em->refresh($questionnaire);

        $form = $this->formFactory->create(QuestionnaireConfigurationUpdateType::class, $questionnaire);

        unset($arguments['questionnaireId']);
        $this->handleQuestions($form, $questionnaire, $arguments, 'questionnaire');
        $this->em->flush();
    }

    public function unsetJumps(array &$questionnaire): void
    {
        if (empty($questionnaire['questions'])) {
            return;
        }

        foreach ($questionnaire['questions'] as &$questionData) {
            unset($questionData['question']['jumps']);
        }
    }

    public function parseTemporaryId(array &$questions)
    {
        foreach ($questions as &$questionData) {
            $questionTempId = $questionData['question']['temporaryId'] ?? null;
            $questionId = $questionData['question']['id'] ?? null;

            if ($questionTempId && !$questionId) {
                $questionData['question']['id'] = $this->getQuestionGlobalId($questionTempId);
            }

            if ($questionData['question']['choices'] ?? null) {
                foreach ($questionData['question']['choices'] as &$choice) {
                    $tempId = $choice['temporaryId'] ?? null;
                    if ($tempId) {
                        $choice['id'] = $this->getQuestionChoiceGlobalId($tempId);
                        unset($choice['temporaryId']);
                    }
                }
            }

            $alwaysJumpDestinationQuestion = $questionData['question']['alwaysJumpDestinationQuestion'] ?? null;
            if ($alwaysJumpDestinationQuestion) {
                $questionData['question']['alwaysJumpDestinationQuestion'] = $this->getQuestionGlobalId($alwaysJumpDestinationQuestion);
            }

            $jumps = $questionData['question']['jumps'] ?? null;
            if ($jumps) {
                foreach ($questionData['question']['jumps'] as &$jump) {
                    $origin = $jump['origin'];
                    if ($origin) {
                        $jump['origin'] = $this->getQuestionGlobalId($origin);
                    }
                    $destination = $jump['destination'];
                    if ($destination) {
                        $jump['destination'] = $this->getQuestionGlobalId($destination);
                    }
                    foreach ($jump['conditions'] as &$condition) {
                        $conditionQuestion = $condition['question'];
                        if ($conditionQuestion) {
                            $condition['question'] = $this->getQuestionGlobalId($conditionQuestion);
                        }
                        $value = $condition['value'];
                        if ($value) {
                            $condition['value'] = $this->getQuestionChoiceGlobalId($value);
                        }
                    }
                }
            }
        }
    }

    private function getQuestionChoiceGlobalId(string $questionChoiceId): string
    {
        $isGlobalId = (bool) GlobalId::fromGlobalId($questionChoiceId)['type'];

        if ($isGlobalId) {
            return $questionChoiceId;
        }

        $questionChoice = $this->questionChoiceRepository->findOneBy(['temporaryId' => $questionChoiceId]);

        if (!$questionChoice instanceof QuestionChoice) {
            throw new \Exception("question choice with id : {$questionChoiceId} was not found");
        }

        return GlobalId::toGlobalId('QuestionChoice', $questionChoice->getId());
    }

    private function getQuestionGlobalId(string $questionId): string
    {
        $isGlobalId = (bool) GlobalId::fromGlobalId($questionId)['type'];

        if ($isGlobalId) {
            return $questionId;
        }

        $question = $this->questionRepository->findOneBy(['temporaryId' => $questionId]);

        if (!$question instanceof AbstractQuestion) {
            throw new \Exception("question with id : {$questionId} was not found");
        }

        return GlobalId::toGlobalId('Question', $question->getId());
    }
}
