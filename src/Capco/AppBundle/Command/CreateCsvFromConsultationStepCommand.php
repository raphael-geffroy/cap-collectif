<?php

namespace Capco\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class CreateCsvFromConsultationStepCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('capco:export:consultation')
            ->setDescription('Create csv file from consultation step data')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $resolver = $this->getContainer()->get('capco.project.download.resolver');

        $steps = $this->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('CapcoAppBundle:Steps\ConsultationStep')
            ->findAll()
        ;

        foreach ($steps as $cs) {
            $writer = $resolver->getContent($cs, 'csv');
            $date = (new \DateTime())->format('Y-m-d');
            $filename = $date.'_';
            if ($cs->getProject()) {
                $filename .= $cs->getProject()->getSlug().'_';
            }
            $filename .= $cs->getSlug().'.csv';
            $path = $this->getContainer()->getParameter('kernel.root_dir');
            $writer->save($path.'/../web/export/'.$filename);
            $output->writeln('The export file "'.$filename.'" has been created.');
        }
    }
}
