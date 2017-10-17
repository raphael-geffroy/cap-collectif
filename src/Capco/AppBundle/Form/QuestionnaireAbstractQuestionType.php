<?php

namespace Capco\AppBundle\Form;

use Capco\AppBundle\Entity\Questions\QuestionnaireAbstractQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionnaireAbstractQuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('position', IntegerType::class);
        $builder->add('question');

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            if (!$data = $event->getData()) {
                return;
            }

            $event->getForm()
                ->add('question', 7 === (int) $data->getQuestion()->getType() ? MediaQuestionType::class : SimpleQuestionType::class);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => QuestionnaireAbstractQuestion::class,
        ]);
    }
}
