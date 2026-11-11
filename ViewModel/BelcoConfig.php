<?php declare(strict_types=1);
/**
 * @package     Belco_Hyva
 * @since       version 0.4.4
 * Copyrights 2024 Falcon Media. All rights reserved.
 * https://www.falconmedia.nl
 */

namespace Belco\Hyva\ViewModel;

use Belco\Widget\Model\BelcoCustomer;
use Belco\Widget\Model\BelcoCustomerFactory;
use Magento\Checkout\Helper\Cart;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Builds the Belco widget config for the current customer and cart.
 */
class BelcoConfig implements ArgumentInterface
{
    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var BelcoCustomerFactory */
    protected $widgetBelcoCustomerFactory;

    /** @var Session */
    protected $customerSession;

    /** @var CustomerFactory */
    protected $customerCustomerFactory;

    /** @var Cart */
    protected $checkoutCartHelper;

    /** @var BelcoCustomer */
    protected $belcoCustomer;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $customerSession
     * @param CustomerFactory $customerCustomerFactory
     * @param Cart $checkoutCartHelper
     * @param BelcoCustomerFactory $widgetBelcoCustomerFactory
     */
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

    /**
     * Returns the widget config.
     *
     * @return array
     */
    public function getSectionData()
    {
        return $this->getConfig();
    }

    /**
     * Builds shop, customer and cart data for the widget.
     *
     * @return array
     */
    protected function getConfig()
    {
        $settings = $this->scopeConfig->getValue('belco_settings/general', ScopeInterface::SCOPE_STORE);

        if (empty($settings['shop_id'])) {
            return [];
        }

        $secret = $settings['api_secret'] ?? null;
        $config = [
            'shopId' => $settings['shop_id']
        ];

        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerSession->getCustomer();

            if ($secret) {
                $config['hash'] = hash_hmac('sha256', (string) $customer->getId(), (string) $secret);
            }

            $config = array_merge($config, $this->belcoCustomer->factory($customer));
        }

        if ($cart = $this->getCart()) {
            $config['cart'] = $cart;
        }

        return $config;
    }

    /**
     * Returns the cart items and total, or null for an empty cart.
     *
     * @return array|null
     */
    protected function getCart()
    {
        $cart = $this->checkoutCartHelper->getCart();
        $quote = $cart->getQuote();
        $items = $quote->getAllVisibleItems();
        $config = [
            'items' => [],
            'total' => $quote->getGrandTotal()
        ];

        foreach ($items as $item) {
            $product = $item->getProduct();
            $config['items'][] = [
                'id' => $product->getId(),
                'quantity' => $item->getQty(),
                'name' => $item->getName(),
                'price' => $item->getPrice(),
                'url' => $product->getProductUrl()
            ];
        }

        if (count($config['items'])) {
            return $config;
        }

        return null;
    }
}
