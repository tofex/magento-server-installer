<?php

namespace Tofex\Composer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use InvalidArgumentException;

/**
 * @author  Andreas Knollmann
 */
class MagentoServerInstaller
    extends LibraryInstaller
{
    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        if ($this->composer->getPackage()) {
            return '';
        } else {
            throw new InvalidArgumentException('The root package is not configured properly.');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return $packageType === 'magento-server';
    }
}
