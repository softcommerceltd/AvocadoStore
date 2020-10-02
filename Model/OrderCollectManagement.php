<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use SoftCommerce\Avocado\Api;
use SoftCommerce\Avocado\Api\Data\ClientOrderMetadataInterface;
use SoftCommerce\Avocado\Api\Data\OrderInterface;
use SoftCommerce\Avocado\Api\OrderCollectManagementInterface;
use SoftCommerce\Avocado\Helper\Data;
use SoftCommerce\Avocado\Logger\Logger;
use SoftCommerce\Avocado\Model\Source\Status;

/**
 * Class OrderCollectManagement
 * @package SoftCommerce\Avocado\Model
 */
class OrderCollectManagement extends AbstractManagement implements
    OrderCollectManagementInterface,
    ClientOrderMetadataInterface
{
    /**
     * @var Order\FileProcessorInterface
     */
    private Order\FileProcessorInterface $_fileProcessor;

    /**
     * @var ResourceModel\Order
     */
    private ResourceModel\Order $_resource;

    /**
     * @var array
     */
    private array $_source = [];

    /**
     * OrderCollectManagement constructor.
     * @param ResourceModel\Order $resource
     * @param Data $helper
     * @param DateTime $dateTime
     * @param Logger $logger
     * @param Json|null $serializer
     */
    public function __construct(
        ResourceModel\Order $resource,
        Data $helper,
        DateTime $dateTime,
        Logger $logger,
        ?Json $serializer = null
    ) {
        $this->_resource = $resource;
        parent::__construct($helper, $dateTime, $logger, $serializer);
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getSource() : array
    {
        if (!$this->_source) {
            throw new LocalizedException(__('Source data is not set.'));
        }
        return $this->_source;
    }

    /**
     * @param array $source
     * @return $this|OrderCollectManagement
     */
    public function setSource(array $source)
    {
        $this->_source = $source;
        return $this;
    }

    /**
     * @param null $orderId
     * @param null $entity
     * @param bool $unserialised
     * @return array|bool|float|int|mixed|string|null
     */
    public function getRequest(
        $orderId = null,
        $entity = null,
        $unserialised = false
    ) {
        if (null === $orderId && null === $entity) {
            return $this->_request ?: [];
        }

        $result = $orderId !== null
            ? ($this->_request[$orderId] ?? [])
            : ($this->_request ?: []);

        if (false !== $unserialised && isset($result[$entity]) && is_string($result[$entity])) {
            try {
                $result = $this->_serializer->unserialize($result[$entity]);
            } catch (\InvalidArgumentException $e) {
                $result = [];
            }
        }

        return $result ?: [];
    }

    /**
     * @param $value
     * @param null $key
     * @return $this
     */
    public function setRequest($value, $key = null)
    {
        null !== $key
            ? $this->_request[$key] = $value
            : $this->_request = $value;
        return $this;
    }

    /**
     * @return $this|OrderCollectManagement
     * @throws LocalizedException
     * @throws FileSystemException
     */
    public function execute()
    {
        $this->executeBefore();

        if (!$this->_helper->getIsActive()) {
            return $this;
        }

        /*
        $this->_fileProcessor->downloadSource($this->getSource());
        if (!$sourceData = $this->_fileProcessor->getSourceData()) {
            return $this;
        }*/

        try {
            $this->_buildRequest()
                ->_submit();
        } catch (\Exception $e) {
            $this->_logger->log(100, __METHOD__, [$e->getMessage()]);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    private function _buildRequest()
    {
        $sourceData = $this->getSource();
        $requestIds = array_unique(array_column($sourceData, ClientOrderMetadataInterface::ORDER_ID));
        $requestIds = array_map(function ($data) {
            $data = explode('-', $data);
            return array_shift($data);
        }, $requestIds);

        $adapter = $this->_resource->getConnection();
        $select = $adapter->select()
            ->from($this->_resource->getMainTable(), [Api\Data\OrderInterface::AVOCADO_ORDER_ID])
            ->where(Api\Data\OrderInterface::AVOCADO_ORDER_ID . ' IN (?)', $requestIds);
        $existingIds = $adapter->fetchCol($select);

        foreach ($sourceData as $item) {
            if (!isset($item[ClientOrderMetadataInterface::ORDER_ID], $item[ClientOrderMetadataInterface::ITEM_ID])) {
                continue;
            }

            $orderId = explode('-', $item[ClientOrderMetadataInterface::ORDER_ID]);
            $orderId = array_shift($orderId);

            if (in_array($orderId, $existingIds)) {
                continue;
            }

            $requestOrderEntry = $this->getRequest($orderId, OrderInterface::ORDER_ENTRY, true) ?: [
                    ClientOrderMetadataInterface::ORDER_ID => $item[ClientOrderMetadataInterface::ORDER_ID],
                    ClientOrderMetadataInterface::PURCHASE_DATE => $item[ClientOrderMetadataInterface::PURCHASE_DATE] ?? null,
                    ClientOrderMetadataInterface::PAYMENT_DATE => $item[ClientOrderMetadataInterface::PAYMENT_DATE] ?? null,
                    ClientOrderMetadataInterface::BUYER_NAME => $item[ClientOrderMetadataInterface::BUYER_NAME] ?? null,
                    ClientOrderMetadataInterface::BUYER_ID => $item[ClientOrderMetadataInterface::BUYER_ID] ?? null,
                    ClientOrderMetadataInterface::CURRENCY => $item[ClientOrderMetadataInterface::CURRENCY] ?? null,
                    ClientOrderMetadataInterface::SHIPPING_PRICE => $item[ClientOrderMetadataInterface::SHIPPING_PRICE] ?? null,
                    ClientOrderMetadataInterface::SHIPPING_TAX => $item[ClientOrderMetadataInterface::SHIPPING_TAX] ?? null
                ];

            $requestBillingEntry = $this->getRequest($orderId, OrderInterface::BILLING_ENTRY, true) ?: [
                    ClientOrderMetadataInterface::RECIPIENT_NAME => $item[ClientOrderMetadataInterface::RECIPIENT_NAME] ?? null,
                    ClientOrderMetadataInterface::BILL_ADDRESS_1 => $item[ClientOrderMetadataInterface::BILL_ADDRESS_1] ?? null,
                    ClientOrderMetadataInterface::BILL_ADDRESS_2 => $item[ClientOrderMetadataInterface::BILL_ADDRESS_2] ?? null,
                    ClientOrderMetadataInterface::BILL_CITY => $item[ClientOrderMetadataInterface::BILL_CITY] ?? null,
                    ClientOrderMetadataInterface::BILL_POSTCODE => $item[ClientOrderMetadataInterface::BILL_POSTCODE] ?? null,
                    ClientOrderMetadataInterface::BILL_COUNTRY => $item[ClientOrderMetadataInterface::BILL_COUNTRY] ?? null,
                    ClientOrderMetadataInterface::BILL_ADDRESS_ADDITIONAL => $item[ClientOrderMetadataInterface::BILL_ADDRESS_ADDITIONAL] ?? null,
                    ClientOrderMetadataInterface::BILL_ADDRESS_STREET => $item[ClientOrderMetadataInterface::BILL_ADDRESS_STREET] ?? null,
                    ClientOrderMetadataInterface::BILL_ADDRESS_NO => $item[ClientOrderMetadataInterface::BILL_ADDRESS_NO] ?? null
                ];

            $requestShippingEntry = $this->getRequest($orderId, OrderInterface::SHIPPING_ENTRY, true) ?: [
                    ClientOrderMetadataInterface::RECIPIENT_NAME => $item[ClientOrderMetadataInterface::RECIPIENT_NAME] ?? null,
                    ClientOrderMetadataInterface::SHIP_ADDRESS_1 => $item[ClientOrderMetadataInterface::SHIP_ADDRESS_1] ?? null,
                    ClientOrderMetadataInterface::SHIP_ADDRESS_2 => $item[ClientOrderMetadataInterface::SHIP_ADDRESS_2] ?? null,
                    ClientOrderMetadataInterface::SHIP_CITY => $item[ClientOrderMetadataInterface::SHIP_CITY] ?? null,
                    ClientOrderMetadataInterface::SHIP_POSTCODE => $item[ClientOrderMetadataInterface::SHIP_POSTCODE] ?? null,
                    ClientOrderMetadataInterface::SHIP_COUNTRY => $item[ClientOrderMetadataInterface::SHIP_COUNTRY] ?? null,
                    ClientOrderMetadataInterface::SHIP_ADDRESS_ADDITIONAL => $item[ClientOrderMetadataInterface::SHIP_ADDRESS_ADDITIONAL] ?? null,
                    ClientOrderMetadataInterface::SHIP_ADDRESS_STREET => $item[ClientOrderMetadataInterface::SHIP_ADDRESS_STREET] ?? null,
                    ClientOrderMetadataInterface::SHIP_ADDRESS_NO => $item[ClientOrderMetadataInterface::SHIP_ADDRESS_NO] ?? null
                ];

            $requestItemEntry = array_merge(
                $this->getRequest($orderId, OrderInterface::ITEM_ENTRY, true) ?: [],
                [
                    [
                        ClientOrderMetadataInterface::ITEM_ID => $item[ClientOrderMetadataInterface::ITEM_ID] ?? null,
                        ClientOrderMetadataInterface::SKU => $item[ClientOrderMetadataInterface::SKU] ?? null,
                        ClientOrderMetadataInterface::PRODUCT_NAME => $item[ClientOrderMetadataInterface::PRODUCT_NAME] ?? null,
                        ClientOrderMetadataInterface::QTY_PURCHASED => $item[ClientOrderMetadataInterface::QTY_PURCHASED] ?? null,
                        ClientOrderMetadataInterface::ITEM_PRICE => $item[ClientOrderMetadataInterface::ITEM_PRICE] ?? null,
                        ClientOrderMetadataInterface::ITEM_TAX => $item[ClientOrderMetadataInterface::ITEM_TAX] ?? null
                    ]
                ]
            );

            $this->setRequest(
                [
                    OrderInterface::AVOCADO_ORDER_ID => $orderId,
                    OrderInterface::STATUS => Status::PENDING,
                    OrderInterface::MESSAGE => __('Order has been collected. Waiting import.'),
                    OrderInterface::ORDER_ENTRY => $this->_serializer->serialize($requestOrderEntry),
                    OrderInterface::BILLING_ENTRY => $this->_serializer->serialize($requestBillingEntry),
                    OrderInterface::SHIPPING_ENTRY => $this->_serializer->serialize($requestShippingEntry),
                    OrderInterface::ITEM_ENTRY => $this->_serializer->serialize($requestItemEntry),
                    OrderInterface::UPDATED_AT => $this->_dateTime->gmtDate(),
                    OrderInterface::COLLECTED_AT => $this->_dateTime->gmtDate()
                ],
                $orderId
            );
        }

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    private function _submit()
    {
        if (!$this->getRequest()) {
            return $this;
        }

        $this->_resource->insertOnDuplicate($this->getRequest());
        return $this;
    }
}
