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
        echo "Initialize vendor directory\n";
        $this->initializeVendorDir();

        $downloadPath = $this->getInstallPath($package);
        echo sprintf("Download path: %s\n", $downloadPath);

        $files = $this->readDirectory($downloadPath, true, true);
        echo sprintf("Found %d files in download path\n", count($files));

        foreach ($files as $file) {
            echo sprintf("Checking file: %s\n", $file);

            if (basename($file) === 'composer.json') {
                continue;
            }

            $targetFile = str_replace('vendor/tofex/magento-server/', '', $file);
            echo sprintf("Target file: %s\n", $targetFile);

            if (file_exists($targetFile)) {
                echo sprintf("Deleting file: %s\n", $targetFile);
                @unlink($targetFile);
            }
        }

        $result = parent::install($repo, $package);

        $files = $this->readDirectory($downloadPath, true, true);
        echo sprintf("Found %d files in download path\n", count($files));

        foreach ($files as $file) {
            echo sprintf("Checking file: %s\n", $file);

            if (basename($file) === 'composer.json') {
                continue;
            }

            $targetFile = str_replace('vendor/tofex/magento-server/', '', $file);
            echo sprintf("Target file: %s\n", $targetFile);

            $this->filesystem->ensureDirectoryExists(dirname($targetFile));

            copy($file, $targetFile);
            echo sprintf("Copying file: %s to: %s\n", $file, $targetFile);

            if (preg_match('/\.sh$/', $targetFile) || preg_match('/\/ini$/', dirname($targetFile))) {
                echo sprintf("Changing file: %s to executable\n", $targetFile);
                system(sprintf('chmod +x %s', $targetFile));
            }
        }

        return $result;
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
            if (basename($file) === 'composer.json') {
                continue;
            }

            $targetFile = str_replace('vendor/tofex/magento-server/', '', $file);

            if (file_exists($targetFile)) {
                @unlink($targetFile);
            }
        }

        $result = parent::update($repo, $initial, $target);

        $downloadPath = $this->getInstallPath($target);

        $files = $this->readDirectory($downloadPath, true, true);

        foreach ($files as $file) {
            if (basename($file) === 'composer.json') {
                continue;
            }

            $targetFile = str_replace('vendor/tofex/magento-server/', '', $file);

            $this->filesystem->ensureDirectoryExists(dirname($targetFile));

            copy($file, $targetFile);

            if (preg_match('/\.sh$/', $targetFile) || preg_match('/\/ini$/', dirname($targetFile))) {
                system(sprintf('chmod +x %s', $targetFile));
            }
        }

        return $result;
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
            if (basename($file) === 'composer.json') {
                continue;
            }

            $targetFile = str_replace('vendor/tofex/magento-server/', '', $file);

            if (file_exists($targetFile)) {
                @unlink($targetFile);
            }
        }

        $result = parent::uninstall($repo, $package);

        return $result;
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
        $files = [];

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
