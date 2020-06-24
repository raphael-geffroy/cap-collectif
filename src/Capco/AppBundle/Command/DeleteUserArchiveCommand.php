<?php

namespace Capco\AppBundle\Command;

use Capco\AppBundle\Entity\UserArchive;
use Capco\AppBundle\Repository\UserArchiveRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class DeleteUserArchiveCommand extends Command
{
    private EntityManagerInterface $em;
    private UserArchiveRepository $userArchiveRepository;
    private Filesystem $filesystem;
    private string $projectDir;

    public function __construct(
        ?string $name,
        EntityManagerInterface $em,
        UserArchiveRepository $userArchiveRepository,
        Filesystem $filesystem,
        string $projectRootDir
    ) {
        $this->em = $em;
        $this->userArchiveRepository = $userArchiveRepository;
        $this->filesystem = $filesystem;
        $this->projectDir = $projectRootDir;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('capco:user_archives:delete')->setDescription(
            'Delete the archive datas requested by a user'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $currDate = new \DateTime();
        $dateToDelete = $currDate->modify('-7 days');

        $output->writeln('Retrieving archives ...');
        $archives = $this->userArchiveRepository->getArchivesToDelete($dateToDelete);

        $output->writeln(\count($archives) . ' archives to delete.');
        $progress = new ProgressBar($output, \count($archives));

        $deleteDate = new \DateTime();

        foreach ($archives as $archive) {
            $archive->setDeletedAt($deleteDate);

            $this->removeArchiveFile($archive);
            $progress->advance();
        }

        $this->em->flush();

        $output->writeln('Old users archives are deleted !');

        return 0;
    }

    protected function removeArchiveFile(UserArchive $archive)
    {
        $zipFile = $this->projectDir . '/public/export/' . $archive->getPath();
        if ($this->filesystem->exists($zipFile)) {
            $this->filesystem->remove($zipFile);
        }
    }
}
