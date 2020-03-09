<?php

namespace Capco\AppBundle\Form\Persister;

use Capco\AppBundle\Entity\District\ProjectDistrictPositioner;
use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Repository\ProjectDistrictPositionerRepository;
use Capco\AppBundle\Repository\ProjectDistrictRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProjectDistrictsPersister
{
    private $districtPositionerRepository;
    private $projectDistrictRepository;
    private $em;

    public function __construct(
        ProjectDistrictPositionerRepository $districtPositionerRepository,
        ProjectDistrictRepository $projectDistrictRepository,
        EntityManagerInterface $em
    ) {
        $this->districtPositionerRepository = $districtPositionerRepository;
        $this->projectDistrictRepository = $projectDistrictRepository;
        $this->em = $em;
    }

    public function persist(array $districtsIds, Project $project)
    {
        $districtEntities = $this->projectDistrictRepository->findByIds($districtsIds);
        $oldPositioners = $this->districtPositionerRepository->findBy([
            'project' => $project->getId()
        ]);
        foreach ($oldPositioners as $positioner) {
            $this->em->remove($positioner);
        }
        $this->em->flush();
        $this->em->refresh($project);
        foreach ($districtEntities as $district) {
            $positioner = new ProjectDistrictPositioner();
            $positioner
                ->setProject($project)
                ->setDistrict($district)
                ->setPosition(array_search($district->getId(), $districtsIds, true));
            $this->em->persist($positioner);
        }
    }
}