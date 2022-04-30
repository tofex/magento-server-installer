<?php

namespace Tofex\Composer;

use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PostFileDownloadEvent;
use Composer\Plugin\PreFileDownloadEvent;
use Composer\Repository\InstalledRepositoryInterface;

/**
 * @author  Andreas Knollmann
 */
class MagentoServerInstaller
    extends LibraryInstaller
    implements EventSubscriberInterface
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

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     * * The method name to call (priority defaults to 0)
     * * An array composed of the method name to call and the priority
     * * An array of arrays composed of the method names to call and respective
     *   priorities, or 0 if unset
     *
     * For instance:
     *
     * * array('eventName' => 'methodName')
     * * array('eventName' => array('methodName', $priority))
     * * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array<string, string|array{0: string, 1?: int}|array<array{0: string, 1?: int}>> The event names to
     *                       listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::PRE_FILE_DOWNLOAD  => [['onPreFileDownload', 0]],
            PluginEvents::POST_FILE_DOWNLOAD => [['onPostFileDownload', 0]]
        ];
    }

    /**
     * @param PreFileDownloadEvent $event
     */
    public function onPreFileDownload(PreFileDownloadEvent $event)
    {
        $type = $event->getType();
        echo sprintf("Type: %s\n", $type);

        $package = $event->getContext();
        echo sprintf("Context package class: %s\n", get_class($package));

        if ($package instanceof PackageInterface) {
            $downloadPath = $this->getInstallPath($package);

            echo sprintf("onPreFileDownload download path: %s\n", $downloadPath);
        }
    }

    /**
     * @param PostFileDownloadEvent $event
     */
    public function onPostFileDownload(PostFileDownloadEvent $event)
    {
        $type = $event->getType();
        echo sprintf("Type: %s\n", $type);

        $package = $event->getContext();
        echo sprintf("Context package class: %s\n", get_class($package));

        if ($package instanceof PackageInterface) {
            $downloadPath = $this->getInstallPath($package);

            echo sprintf("onPostFileDownload download path: %s\n", $downloadPath);
        }
    }
}
