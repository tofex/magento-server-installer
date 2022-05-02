<?php

namespace Tofex\Composer\Installers;

use Composer\Composer;
use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Installer
    extends LibraryInstaller
{
    /**
     * {@inheritDoc}
     */
    public function supports($packageType): bool
    {
        return $packageType === 'magento-server-component';
    }

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package): string
    {
        if ($this->composer instanceof Composer) {
            $installer = new MagentoServerInstaller($package, $this->composer, $this->io);

            return $installer->getInstallPath($package, 'magento-server');
        }

        return parent::getInstallPath($package);
    }

    /**
     * @inheritDoc
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        echo sprintf("Update Repo: %s\n", $repo->getRepoName());
        echo sprintf("Update Initial Name: %s\n", $initial->getName());
        echo sprintf("Update Initial Binaries: \n%s\n", implode("\n", $initial->getBinaries()));

        return parent::update($repo, $initial, $target);
    }

    /**
     * @inheritDoc
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        echo sprintf("Uninstall Repo: %s\n", $repo->getRepoName());
        echo sprintf("Uninstall Package Name: %s\n", $package->getName());
        echo sprintf("Uninstall Package Version: %s\n", $package->getVersion());
        echo sprintf("Uninstall Package Binaries: \n%s\n", implode("\n", $package->getBinaries()));

        return parent::uninstall($repo, $package);
    }
}
