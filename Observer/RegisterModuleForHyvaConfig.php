<?php declare(strict_types=1);

/**
 * @author      Henk Valk (henk@falconmedia.nl)
 * @author      Ruud van Zuidam (allrude@gmail.com)
 * @package     Belco_Hyva
 * @since       version 0.4
 * Copyrights  2024 Falcon Media. All rights reserved.
 * https://www.falconmedia.nl
 */

namespace Belco\Hyva\Observer;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RegisterModuleForHyvaConfig implements ObserverInterface
{
    private ComponentRegistrar $componentRegistrar;

    public function __construct(ComponentRegistrar $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
    }

    public function execute(Observer $event)
    {
        $config = $event->getData('config');
        $extensions = $config->hasData('extensions') ? $config->getData('extensions') : [];

        $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Belco_Hyva');

        // Only use the path relative to the Magento base dir
        $extensions[] = ['src' => substr($path, strlen(BP) + 1)];

        $config->setData('extensions', $extensions);
    }
}
