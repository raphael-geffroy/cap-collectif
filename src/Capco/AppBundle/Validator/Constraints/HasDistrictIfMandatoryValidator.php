<?php

namespace Capco\AppBundle\Validator\Constraints;

use Capco\AppBundle\Toggle\Manager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class HasDistrictIfMandatoryValidator extends ConstraintValidator
{
    private $toggleManager;

    public function __construct(Manager $toggleManager)
    {
        $this->toggleManager = $toggleManager;
    }

    public function validate($object, Constraint $constraint)
    {
        if ($object->getDistrict()) {
            return true;
        }

        $form = $object->getProposalForm();

        if ($this->toggleManager->isActive('districts') && $form && $form->isUsingDistrict() && $form->isDistrictMandatory()) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;

            return false;
        }

        return true;
    }
}
