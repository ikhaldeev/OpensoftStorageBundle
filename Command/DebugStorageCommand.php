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
use Opensoft\StorageBundle\Entity\Repository\StorageRepository;
use Opensoft\StorageBundle\Entity\Storage;
use Opensoft\StorageBundle\Entity\StorageFile;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
class DebugStorageCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('debug:storage');
        $this->setAliases(['storage:debug']);
        $this->setDescription('Display storage system and information');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return integer null|int     null or 0 if everything went fine, or an error code
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Storage[] $storages */
        $storages = $this->getStorageRepository()->findAll();

        $output->writeln(sprintf("Showing <comment>%d</comment> known file types:", count(StorageFile::$types)));
        foreach (StorageFile::$types as $key => $type) {
            $output->writeln(sprintf("  <comment>[%d]</comment> %s", $key, $type));
        }

        $output->writeln(sprintf("\nShowing <comment>%d</comment> configured storage locations:", count($storages)));
        foreach ($storages as $storage) {
            $stats = $this->getStorageFileRepository()->statsByStorage($storage);

            $output->writeln(sprintf("  <comment>[%d]</comment> %s", $storage->getId(), $storage->getName()));
            $output->writeln(sprintf("     internal identifier - %s", $storage->getSlug()));
            $output->writeln(sprintf("     default write policy? - %s", $storage->isActive() ? '<info>Yes</info>' : '<error>No</error>'));
            $output->writeln(sprintf("     created - %s", $storage->getCreatedAt()->format(DATE_ISO8601)));
            $output->writeln(sprintf("     files - %d", $stats['file_count']));
            $output->writeln(sprintf("     stored bytes - %d", $stats['file_size']));
            $output->writeln(sprintf("     %s", $storage->getAdapterType()));

            foreach ($storage->getAdapterOptions() as $key => $value) {
                $output->writeln(sprintf("       %s = %s", $key, $value));
            }
        }
    }

    /**
     * @return StorageRepository
     */
    private function getStorageRepository()
    {
        return $this->getContainer()->get('opensoft_onp_core.repository.storage_repository');
    }

    /**
     * @return StorageFileRepository
     */
    private function getStorageFileRepository()
    {
        return $this->getContainer()->get('opensoft_onp_core.repository.storage_file_repository');
    }
}
