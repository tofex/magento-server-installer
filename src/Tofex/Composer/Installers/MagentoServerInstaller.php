<?php

namespace Tofex\Composer\Installers;

use Composer\Installers\BaseInstaller;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class MagentoServerInstaller
    extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = [
        'component' => '{$name}/'
    ];
}
