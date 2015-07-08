<?php

namespace Capco\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ConsultationAbstractStepAdmin extends Admin
{
    protected $formOptions = array(
        'cascade_validation' => true,
    );

    protected $translationDomain = 'SonataAdminBundle';

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $consultationId = null;

        if ($this->hasParentFieldDescription()) { // this Admin is embedded
            $consultation = $this->getParentFieldDescription()->getAdmin()->getSubject();
            if ($consultation) {
                $consultationId = $consultation->getId();
            }
        }

        $formMapper
            ->add('position', null, array(
                'label' => 'admin.fields.consultation_abstractstep.position',
            ))
            ->add('step', 'sonata_type_model_list', array(
                'required' => true,
                'label' => 'admin.fields.consultation_abstractstep.steps',
                'translation_domain' => 'SonataAdminBundle',
                'btn_delete' => false,
                'btn_add' => 'admin.fields.consultation_abstractstep.steps_add',
            ), array(
               'link_parameters' => ['consultation_id' => $consultationId],
            ))
        ;
    }

    public function postRemove($object)
    {
        // delete linked step
        if ($object->getStep()) {
            $em = $this->getConfigurationPool()->getContainer()->get('doctrine.orm.entity_manager');
            $em->remove($object->getStep());
        }
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('create', 'delete', 'edit'));
    }
}
