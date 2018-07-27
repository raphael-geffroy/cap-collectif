<?php
namespace Capco\AppBundle\GraphQL\Resolver\QuestionChoice;

use Capco\AppBundle\Entity\QuestionChoice;
use Capco\AppBundle\Repository\AbstractResponseRepository;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class QuestionChoiceRankingResolver implements ResolverInterface
{
    private $responseRepository;

    public function __construct(AbstractResponseRepository $responseRepository)
    {
        $this->responseRepository = $responseRepository;
    }

    public function __invoke(QuestionChoice $questionChoice): ?array
    {
        if (
            $questionChoice->getQuestion() &&
            'ranking' !== $questionChoice->getQuestion()->getInputType()
        ) {
            return null;
        }

        $responses = $this->responseRepository->findBy([
            'question' => $questionChoice->getQuestion(),
        ]);

        $rankingArray = [];

        foreach ($responses as $response) {
            $responseValue = $response ? $response->getValue() : null;
            if (
                $responseValue &&
                isset($responseValue['labels']) &&
                null !== $responseValue['labels']
            ) {
                $position = 0;
                foreach ($responseValue['labels'] as $label) {
                    ++$position;
                    if ($label === $questionChoice->getTitle()) {
                        $rankingArray[$position]['position'] = $position;
                        $rankingArray[$position]['answerCount'] = isset(
                            $rankingArray[$position]['answerCount']
                        )
                            ? ++$rankingArray[$position]['answerCount']
                            : 1;
                    }
                }
            }
        }
        return $rankingArray;
    }
}
