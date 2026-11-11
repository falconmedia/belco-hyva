<?php declare(strict_types=1);
/**
 * @package     Belco_Hyva
 * @since       version 0.4.7
 * Copyrights 2025 Falcon Media. All rights reserved.
 * https://www.falconmedia.nl
 */

namespace Belco\Hyva\Controller\Config;

use Belco\Hyva\ViewModel\BelcoConfig;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\PageCache\NotCacheableInterface;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;

/**
 * Uncached JSON endpoint with the Belco widget config.
 */
class Index implements HttpGetActionInterface, NotCacheableInterface
{
    /**
     * @param ResultFactory $resultFactory
     * @param LoggerInterface $logger
     * @param BelcoConfig $belcoConfig
     */
    public function __construct(
        private readonly ResultFactory $resultFactory,
        private readonly LoggerInterface $logger,
        private readonly BelcoConfig $belcoConfig
    ) {
    }

    /**
     * Returns the customer-specific widget config; the private, no-store headers keep it out of FPC and Varnish.
     *
     * @return JsonResult
     */
    public function execute(): JsonResult
    {
        try {
            $data = $this->belcoConfig->getSectionData();
        } catch (\Throwable $e) {
            // Never leak sensitive details
            $this->logger->error('[BelcoHyva] Config endpoint error: ' . $e->getMessage());
            $data = ['status' => 'error'];
        }

        /** @var JsonResult $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setHeader('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0', true);
        $result->setHeader('Pragma', 'no-cache', true);
        $result->setHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT', true);
        $result->setData($data);

        return $result;
    }
}
