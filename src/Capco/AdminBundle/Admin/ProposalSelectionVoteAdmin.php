<?php

namespace Capco\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class ProposalSelectionVoteAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('createdAt', null, ['label' => 'admin.fields.opinion_vote.created_at'])
            ->add('proposal', null, ['label' => 'admin.fields.proposal'])
            ->add('selectionStep', null, ['label' => 'admin.fields.selectionStep'])
            ->add(
                'user',
                'doctrine_orm_model_autocomplete',
                ['label' => 'admin.fields.argument_vote.voter'],
                null,
                [
                    'property' => 'email,username',
                    'to_string_callback' => function ($enitity, $property) {
                        return $enitity->getEmail() . ' - ' . $enitity->getUsername();
                    },
                ]
            );
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        unset($this->listModes['mosaic']);

        $listMapper
            ->add('proposal', 'sonata_type_model', ['label' => 'admin.fields.proposal'])
            ->add('user', 'sonata_type_model', ['label' => 'admin.fields.argument_vote.voter'])
            ->add('collectStep', 'sonata_type_model', ['label' => 'admin.fields.step'])
            ->add('createdAt', null, ['label' => 'admin.fields.opinion_vote.created_at'])
            ->add('private', null, ['label' => 'admin.global.private'])
            ->add('username', null, ['label' => 'admin.global.username'])
            ->add('email', null, ['label' => 'admin.global.email'])
            ->add('_action', 'actions', ['actions' => ['show' => []]]);
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('proposal', 'sonata_type_model', [
                'label' => 'admin.fields.argument_vote.argument',
            ])
            ->add('user', 'sonata_type_model', ['label' => 'admin.fields.argument_vote.voter'])
            ->add('createdAt', null, ['label' => 'admin.fields.argument_vote.created_at']);
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('edit');
    }
}
