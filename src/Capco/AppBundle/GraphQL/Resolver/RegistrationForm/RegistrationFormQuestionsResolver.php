<?php

namespace Capco\AppBundle\GraphQL\Resolver\RegistrationForm;

use Capco\AppBundle\Entity\RegistrationForm;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class RegistrationFormQuestionsResolver implements ResolverInterface
{
    public function __invoke(RegistrationForm $form): array
    {
        $questions = $form->getRealQuestions()->toArray();
        usort($questions, function ($a, $b) {
            return $a->getQuestionnaireAbstractQuestion()->getPosition() <=>
                $b->getQuestionnaireAbstractQuestion()->getPosition();
        });

        return $questions;
    }
}
