<?php

namespace Tofex\Composer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

/**
 * @author  Andreas Knollmann
 */
class MagentoServerInstaller
    extends LibraryInstaller
{
    /**
     * {@inheritDoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->initializeVendorDir();
        $downloadPath = $this->getInstallPath($package);

        $files = $this->readDirectory($downloadPath, true, true);
        foreach ($files as $file) {
            $targetFile = str_replace('vendor/tofex/magento-server/', '', $file);
            if (file_exists($targetFile)) {
                @unlink($targetFile);
            }
        }

        parent::install($repo, $package);

        $files = $this->readDirectory($downloadPath, true, true);
        foreach ($files as $file) {
            $targetFile = str_replace('vendor/tofex/magento-server/', '', $file);
            $this->filesystem->ensureDirectoryExists(dirname($targetFile));
            copy($file, $targetFile);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        $this->initializeVendorDir();
        $downloadPath = $this->getInstallPath($initial);

        $files = $this->readDirectory($downloadPath, true, true);
        foreach ($files as $file) {
            $targetFile = str_replace('vendor/tofex/magento-server/', '', $file);
            if (file_exists($targetFile)) {
                @unlink($targetFile);
            }
        }

        parent::update($repo, $initial, $target);

        $downloadPath = $this->getInstallPath($target);

        $files = $this->readDirectory($downloadPath, true, true);
        foreach ($files as $file) {
            $targetFile = str_replace('vendor/tofex/magento-server/', '', $file);
            $this->filesystem->ensureDirectoryExists(dirname($targetFile));
            copy($file, $targetFile);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->initializeVendorDir();
        $downloadPath = $this->getInstallPath($package);

        $files = $this->readDirectory($downloadPath, true, true);
        foreach ($files as $file) {
            $targetFile = str_replace('vendor/tofex/magento-server/', '', $file);
            if (file_exists($targetFile)) {
                @unlink($targetFile);
            }
        }

        parent::uninstall($repo, $package);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return $packageType === 'magento-server';
    }

    /**
     * @param string $useDirectoryPath
     * @param bool   $useRecursive
     * @param bool   $useFilesOnly
     * @param string $usePattern
     *
     * @return array
     */
    protected function readDirectory(
        $useDirectoryPath,
        $useRecursive = true,
        $useFilesOnly = false,
        $usePattern = null)
    {
        $files = array();

        if (file_exists($useDirectoryPath)) {
            $directoryFiles = preg_grep('/^\.+$/', scandir($useDirectoryPath), PREG_GREP_INVERT);

            foreach ($directoryFiles as $directoryFile) {
                if ($useFilesOnly === false ||
                    ($useFilesOnly === true && is_file($useDirectoryPath . '/' . $directoryFile))) {
                    array_push($files, $useDirectoryPath . '/' . $directoryFile);
                }

                if ($useRecursive && is_dir($useDirectoryPath . '/' . $directoryFile)) {
                    $subDirectoryFiles =
                        $this->readDirectory($useDirectoryPath . '/' . $directoryFile, $useRecursive, $useFilesOnly,
                            $usePattern);

                    foreach ($subDirectoryFiles as $subDirectoryFile) {
                        array_push($files, $subDirectoryFile);
                    }
                }
            }

            if ($usePattern !== null) {
                $files = preg_grep("/$usePattern/", $files);
            }
        }

        return $files;
    }
}
