<?php

namespace Capco\AppBundle\Command;

use Capco\AppBundle\Entity\Opinion;
use Capco\AppBundle\Entity\OpinionModal;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportConsultationModalsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('capco:import:consultation-modals-from-csv')
            ->setDescription('Import consultation modals from CSV file for consultation step')
            ->addArgument(
                'filePath',
                InputArgument::REQUIRED,
                'Please provide the path of the file you want to use.'
            )
            ->addArgument(
                'step',
                InputArgument::REQUIRED,
                'Please provide the slug of the consultation step you want to use'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->import($input, $output);
    }

    protected function import(InputInterface $input, OutputInterface $output)
    {
        $modals = $this->getModals($input->getArgument('filePath'));

        $em = $this->getContainer()->get('doctrine')->getManager();
        $step = $em->getRepository('CapcoAppBundle:Steps\ConsultationStep')
                   ->find($input->getArgument('step'))
                ;

        foreach ($modals as $row) {
            $opinion = $em->getRepository('CapcoAppBundle:Opinion')
                          ->findOneBy([
                                'title' => $row['opinion'],
                                'step' => $step,
                            ]);

            if (!is_object($opinion)) {
                throw new \InvalidArgumentException('Unknown title: ' . $row['opinion'], 1);
            }

            $modal = new OpinionModal();
            $modal->setOpinion($opinion);
            $modal->setTitle($row['title']);
            $modal->setKey($row['key']);
            $modal->setBefore($row['before']);
            $modal->setAfter($row['after']);

            $em->persist($modal);
            $em->flush();
        }
    }

    protected function getModals($filePath)
    {
        return $this->getContainer()
                    ->get('import.csvtoarray')
                    ->convert($filePath);
    }
}
