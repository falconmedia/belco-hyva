<?php declare(strict_types=1);

namespace Belco\Hyva\ViewModel;

use Belco\Widget\Model\BelcoCustomer;
use Belco\Widget\Model\BelcoCustomerFactory;
use Magento\Checkout\Helper\Cart;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class BelcoConfig implements ArgumentInterface
{
    protected $scopeConfig;
    protected $widgetBelcoCustomerFactory;
    protected $customerSession;
    protected $customerCustomerFactory;
    protected $checkoutCartHelper;

    /** @var BelcoCustomer */
    protected $belcoCustomer;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        CustomerFactory $customerCustomerFactory,
        Cart $checkoutCartHelper,
        BelcoCustomerFactory $widgetBelcoCustomerFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->checkoutCartHelper = $checkoutCartHelper;
        $this->belcoCustomer = $widgetBelcoCustomerFactory->create();
    }

    public function getSectionData()
    {
        return $this->getConfig();
    }

    protected function getConfig()
    {
        $settings = $this->scopeConfig->getValue('belco_settings/general', ScopeInterface::SCOPE_STORE);

        if (empty($settings['shop_id'])) {
            return array();
        }

        $secret = $settings['api_secret'];
        $config = array(
            'shopId' => $settings['shop_id']
        );

        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerCustomerFactory->create()->load($this->customerSession->getCustomer()->getId());

            if ($secret) {
                $config['hash'] = hash_hmac("sha256", $customer->getId(), $secret);
            }

            $config = array_merge($config, $this->belcoCustomer->factory($customer));
        }

        if ($cart = $this->getCart()) {
            $config['cart'] = $cart;
        }

        return $config;
    }

    protected function getCart()
    {
        $cart = $this->checkoutCartHelper->getCart();
        $quote = $cart->getQuote();
        $items = $quote->getAllVisibleItems();
        $config = array(
            'items' => array(),
            'total' => $quote->getGrandTotal()
        );

        foreach ($items as $item) {
            $product = $item->getProduct();
            $config['items'][] = array(
                'id' => $product->getId(),
                'quantity' => $item->getQty(),
                'name' => $item->getName(),
                'price' => $item->getPrice(),
                'url' => $product->getProductUrl()
            );
        }

        if (count($config['items'])) {
            return $config;
        }
    }
}
