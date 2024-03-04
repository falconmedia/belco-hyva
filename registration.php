<?php declare(strict_types=1);

/**
 * @author      Henk Valk (henk@falconmedia.nl)
 * @author      Ruud van Zuidam (allrude@gmail.com)
 * @package     Belco_Hyva
 * @since       version 0.4
 * Copyrights  2024 Falcon Media. All rights reserved.
 * https://www.falconmedia.nl
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Belco_Hyva',
    __DIR__
);
