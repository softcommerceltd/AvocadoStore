<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 * @package SoftCommerce\Avocado\Helper
 */
class Data extends AbstractHelper
{
    const XML_PATH_IS_ACTIVE                    = 'softcommerce_avocado/order/is_active';
    const XML_PATH_IS_ACTIVE_CREATE_INVOICE     = 'softcommerce_avocado/order/is_active_create_invoice';
    const XML_PATH_DEFAULT_CUSTOMER_EMAIL       = 'softcommerce_avocado/order/default_customer_email';
    const XML_PATH_DEFAULT_STORE                = 'softcommerce_avocado/order/default_store';
    const XML_PATH_IS_ACTIVE_SHIPMENT           = 'softcommerce_avocado/shipment/is_active';
    const XML_PATH_DEV_IS_ACTIVE_DEBUG          = 'softcommerce_avocado/dev/is_active_debug';
    const XML_PATH_DEV_DEBUG_PRINT_TO_ARRAY     = 'softcommerce_avocado/dev/debug_print_to_array';

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $_storeManager;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    protected function _getStore()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @param $path
     * @param null $store
     * @return mixed
     * @throws NoSuchEntityException
     */
    protected function _getConfig($path, $store = null)
    {
        if (null === $store) {
            $store = $this->_getStore();
        }

        return $this->scopeConfig
            ->getValue($path, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getIsActive()
    {
        return (bool) $this->_getConfig(self::XML_PATH_IS_ACTIVE);
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getIsActiveCreateInvoice()
    {
        return (bool) $this->_getConfig(self::XML_PATH_IS_ACTIVE_CREATE_INVOICE);
    }

    /**
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getDefaultCustomerEmail()
    {
        return $this->_getConfig(self::XML_PATH_DEFAULT_CUSTOMER_EMAIL);
    }

    /**
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getDefaultStore()
    {
        return $this->_getConfig(self::XML_PATH_DEFAULT_STORE);
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getIsActiveShipment()
    {
        return (bool) $this->_getConfig(self::XML_PATH_IS_ACTIVE_SHIPMENT);
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getIsActiveDebug()
    {
        return (bool) $this->_getConfig(self::XML_PATH_DEV_IS_ACTIVE_DEBUG);
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getIsDebugPrintToArray()
    {
        return (bool) $this->_getConfig(self::XML_PATH_DEV_DEBUG_PRINT_TO_ARRAY);
    }
}
