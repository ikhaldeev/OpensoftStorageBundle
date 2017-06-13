<?php
/**
 * This file is part of ONP.
 *
 * Copywrite (c) Opensoft (http://opensoftdev.com)
 *
 * The unauthorized use of this code outside the boundaries of
 * Opensoft is prohibited.
 */
namespace Opensoft\StorageBundle\Command;

use Opensoft\StorageBundle\Entity\Repository\StorageFileRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
class DeleteStorageFileCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('storage:delete-file');
        $this->setDescription("Remove a specific file from the storage system.");
        $this->setHelp('Warning: If the storage file is used by the system and deletion cascade behavior is not defined for this storage file, you will not be able to delete it.');
        $this->addArgument('storageFileId', InputArgument::REQUIRED, 'Storage File ID');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return integer null|int     null or 0 if everything went fine, or an error code
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $storageFileId = $input->getArgument('storageFileId');
        $storageFile = $this->getStorageFileRepository()->find($storageFileId);

        if (!$storageFile) {
            $output->writeln(sprintf("<error>Can not find file with storage id '%d' to delete it</error>", $storageFileId));

            return -1;
        }

        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->remove($storageFile);
        $em->flush();

        $output->writeln(sprintf("<info>Storage file '%d' deleted</info>", $storageFileId));
    }

    /**
     * @return StorageFileRepository
     */
    private function getStorageFileRepository()
    {
        return $this->getContainer()->get('opensoft_onp_core.repository.storage_file_repository');
    }
}
