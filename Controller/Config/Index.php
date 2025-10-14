<?php
namespace Belco\Hyva\Controller\Config;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Belco\Hyva\ViewModel\BelcoConfig;
use Hyva\Theme\Model\ViewModelRegistry;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * @var ViewModelRegistry
     */
    private $viewModelRegistry;

    /**
     * @var BelcoConfig
     */
    private $belcoConfig;

    public function __construct(
        Context $context,
        ViewModelRegistry $viewModelRegistry
    ) {
        parent::__construct($context);
        $this->viewModelRegistry = $viewModelRegistry;
    }

    public function execute()
    {
        // Get Belco config for current user
        $belcoConfig = $this->viewModelRegistry->require(BelcoConfig::class);
        $data = $belcoConfig->getSectionData();

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($data);
        // Prevent caching of this response
        $this->getResponse()->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
        $this->getResponse()->setHeader('Pragma', 'no-cache', true);
        return $resultJson;
    }
}
