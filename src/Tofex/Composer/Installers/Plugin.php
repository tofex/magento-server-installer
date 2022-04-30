<?php

namespace Tofex\Composer\Installers;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Plugin
    implements PluginInterface
{
    /**
     * Apply plugin modifications to Composer
     *
     * @param Composer    $composer
     * @param IOInterface $io
     *
     * @return void
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new Installer($io, $composer);

        $composer->getInstallationManager()->addInstaller($installer);
    }

    /**
     * Remove any hooks from Composer
     *
     * This will be called when a plugin is deactivated before being
     * uninstalled, but also before it gets upgraded to a new version
     * so the old one can be deactivated and the new one activated.
     *
     * @param Composer    $composer
     * @param IOInterface $io
     *
     * @return void
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    /**
     * Prepare the plugin to be uninstalled
     *
     * This will be called after deactivate.
     *
     * @param Composer    $composer
     * @param IOInterface $io
     *
     * @return void
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
    }
}
