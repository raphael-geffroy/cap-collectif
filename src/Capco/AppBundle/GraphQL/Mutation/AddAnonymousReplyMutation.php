<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\Elasticsearch\Indexer;
use Capco\AppBundle\Entity\Questionnaire;
use Capco\AppBundle\Entity\ReplyAnonymous;
use Capco\AppBundle\Form\ReplyAnonymousType;
use Capco\AppBundle\GraphQL\Resolver\GlobalIdResolver;
use Capco\AppBundle\Helper\ResponsesFormatter;
use Capco\AppBundle\Notifier\QuestionnaireReplyNotifier;
use Capco\AppBundle\Utils\RequestGuesser;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\SwarrotBundle\Broker\Publisher;
use Symfony\Component\Form\FormFactoryInterface;

class AddAnonymousReplyMutation implements MutationInterface
{
    public const INVALID_FORM = 'INVALID_FORM';
    private EntityManagerInterface $em;
    private FormFactoryInterface $formFactory;
    private ResponsesFormatter $responsesFormatter;
    private RequestGuesser $requestGuesser;
    private TokenGeneratorInterface $tokenGenerator;
    private GlobalIdResolver $globalIdResolver;
    private Publisher $publisher;
    private Indexer $indexer;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $em,
        FormFactoryInterface $formFactory,
        ResponsesFormatter $responsesFormatter,
        LoggerInterface $logger,
        RequestGuesser $requestGuesser,
        TokenGeneratorInterface $tokenGenerator,
        GlobalIdResolver $globalIdResolver,
        Publisher $publisher,
        Indexer $indexer
    ) {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->responsesFormatter = $responsesFormatter;
        $this->logger = $logger;
        $this->requestGuesser = $requestGuesser;
        $this->tokenGenerator = $tokenGenerator;
        $this->globalIdResolver = $globalIdResolver;
        $this->publisher = $publisher;
        $this->indexer = $indexer;
    }

    public function __invoke(Argument $input): array
    {
        $values = $input->getArrayCopy();

        /** @var Questionnaire $questionnaire */
        $questionnaire = $this->globalIdResolver->resolve($values['questionnaireId'], null);
        unset($values['questionnaireId']);

        $participantEmail = $values['participantEmail'] ?? null;

        $token = $this->tokenGenerator->generateToken();

        $replyAnonymous = (new ReplyAnonymous())
            ->setQuestionnaire($questionnaire)
            ->setNavigator($this->requestGuesser->getUserAgent())
            ->setIpAddress($this->requestGuesser->getClientIp())
            ->setToken($token)
            ->setParticipantEmail($participantEmail)
            ->setPublishedAt(new \DateTime('now'))
        ;

        $values['responses'] = $this->responsesFormatter->format($values['responses']);

        $form = $this->formFactory->create(ReplyAnonymousType::class, $replyAnonymous);
        $form->submit($values, false);

        if (!$form->isValid()) {
            $this->logger->error(__METHOD__ . (string) $form->getErrors(true, false));

            return [
                'questionnaire' => $questionnaire,
                'reply' => null,
                'token' => null,
                'errorCode' => self::INVALID_FORM,
            ];
        }

        $this->em->persist($replyAnonymous);
        $this->em->flush();
        $this->indexer->index(ReplyAnonymous::class, $replyAnonymous->getId());
        $this->indexer->finishBulk();

        if ($questionnaire->isNotifyResponseCreate() || $replyAnonymous->getParticipantEmail()) {
            $this->publisher->publish(
                'questionnaire.reply',
                new Message(
                    json_encode([
                        'replyId' => $replyAnonymous->getId(),
                        'state' => QuestionnaireReplyNotifier::QUESTIONNAIRE_REPLY_CREATE_STATE,
                    ])
                )
            );
        }

        return [
            'questionnaire' => $questionnaire,
            'reply' => $replyAnonymous,
            'token' => $token,
            'errorCode' => null,
        ];
    }
}
