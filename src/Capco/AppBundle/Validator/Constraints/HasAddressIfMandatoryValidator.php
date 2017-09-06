<?php

namespace Capco\AppBundle\Validator\Constraints;

use Capco\AppBundle\Helper\GeometryHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class HasAddressIfMandatoryValidator extends ConstraintValidator
{
    public function validate($object, Constraint $constraint): bool
    {
        $form = $object->getProposalForm();

        if (!$form->getUsingAddress()) {
            return true;
        }

        $address = $object->getAddress();
        if (!$address) {
            $this->context
                ->buildViolation($constraint->noAddressMessage)
                ->addViolation();

            return false;
        }

        $decodedAddress = json_decode($address, true);
        if (!$decodedAddress) {
            $this->context
                ->buildViolation($constraint->noValidJsonAddressMessage)
                ->addViolation();

            return false;
        }

        if (!$form->isProposalInAZoneRequired()) {
            return true;
        }
        $latitude = $decodedAddress[0]['geometry']['location']['lat'];
        $longitude = $decodedAddress[0]['geometry']['location']['lng'];
        foreach ($form->getDistricts() as $district) {
            if ($district->getGeojson() && GeometryHelper::isIncluded($longitude, $latitude, $district->getGeojson())) {
                return true;
            }
        }

        $this->context
              ->buildViolation($constraint->addressNotInZoneMessage)
              ->addViolation();

        return false;
    }
}
