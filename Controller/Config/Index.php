<?php declare(strict_types=1);
/**
 * @package     Belco_Hyva
 * @since       version 0.4.7
 * Copyrights 2025 Falcon Media. All rights reserved.
 * https://www.falconmedia.nl
 */

namespace Belco\Hyva\Controller\Config;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\PageCache\NotCacheableInterface;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\Action\Context;
use Psr\Log\LoggerInterface;
use Belco\Hyva\ViewModel\BelcoConfig;
use Hyva\Theme\Model\ViewModelRegistry;

class Index implements HttpGetActionInterface, NotCacheableInterface
{

    public function __construct(
        private readonly Context $context,
        private readonly ResultFactory $resultFactory,
        private readonly HttpResponse $response,
        private readonly LoggerInterface $logger,
        private readonly ViewModelRegistry $viewModelRegistry
    ) {
    }

    /**
     * @return JsonResult
     */
    public function execute(): JsonResult
    {
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
        $this->response->setHeader('Pragma', 'no-cache', true);
        $this->response->setHeader('Expires', '0', true);

        try {
            $belcoConfig = $this->viewModelRegistry->require(BelcoConfig::class);
            $data = $belcoConfig->getSectionData();

        } catch (\Throwable $e) {
            // Never leak sensitive details
            $this->logger->error('[BelcoHyva] Config endpoint error: ' . $e->getMessage());
            $data = ['status' => 'error'];
        }

        /** @var JsonResult $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData($data);
        return $result;
    }
}
