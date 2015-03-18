<?php

namespace Capco\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Capco\UserBundle\Entity\User;

class IdeaVoteType extends AbstractType
{

    private $user;
    private $confirmed;

    public function __construct(User $user = null, $confirmed) {

        $this->user = $user;
        $this->confirmed = $confirmed;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->confirmed) {

            $builder->add('submit', 'submit', [
              'label' => 'idea.vote.unsubscribe',
              'attr' => ['class' => 'btn  btn-danger  btn-block']
            ]);

          return;
        }

        $builder

        ;


        if ($this->user != null) {

            $builder
              ->add('private', null, [
                  'required' => false,
                  'label' => 'idea.vote.private'
              ])
              ->add('message', null, [
                    'required' => false,
                    'label' => false,
                    'attr' => ['placeholder' => 'idea.vote.message']
              ])
              ->add('submit', 'submit', [
                  'label' => 'idea.vote.submit',
                  'attr' => ['class' => 'btn btn-success btn-block']
              ])
            ;

            return;
        }

        $builder
            ->add('username', null, [
                  'label' => 'idea.vote.name'
            ])
            ->add('email', null, [
                  'label' => 'idea.vote.email'
            ])
            ->add('private', null, [
                  'required' => false,
                  'label' => 'idea.vote.private'
            ])
            ->add('message', null, [
                  'required' => false,
                  'label' => false,
                  'attr' => ['placeholder' => 'idea.vote.message']
            ])
            ->add('submit', 'submit', [
                  'label' => 'idea.vote.submit',
                  'attr' => ['class' => 'btn  btn-success  btn-block']
            ])
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Capco\AppBundle\Entity\IdeaVote',
            'translation_domain' => 'CapcoAppBundle',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'capco_app_idea_vote';
    }
}
