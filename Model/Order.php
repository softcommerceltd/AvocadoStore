<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use SoftCommerce\Avocado\Api\Data\OrderInterface;
use SoftCommerce\Avocado\Model\ResourceModel\Order\Collection;

/**
 * @method ResourceModel\Order getResource()
 * @method Collection getCollection()
 */
class Order extends AbstractModel implements
    OrderInterface,
    IdentityInterface
{
    const CACHE_TAG = 'softcommerce_avocado_order';
    protected $_cacheTag = 'softcommerce_avocado_order';
    protected $_eventPrefix = 'softcommerce_avocado_order';

    /**
     * @var Json|null
     */
    private ?Json $_serializer;

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Order::class);
    }

    /**
     * Order constructor.
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return string|null
     */
    public function getAvocadoOrderId() : string
    {
        return $this->getData(self::AVOCADO_ORDER_ID);
    }

    /**
     * @param string $orderId
     * @return $this
     */
    public function setAvocadoOrderId(string $orderId)
    {
        $this->setData(self::AVOCADO_ORDER_ID, $orderId);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus() : ?string
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param string|null $status
     * @return $this
     */
    public function setStatus(?string $status)
    {
        $this->setData(self::STATUS, $status);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOrderId() : ?int
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @param int|null $orderId
     * @return $this
     */
    public function setOrderId(?int $orderId)
    {
        $this->setData(self::ORDER_ID, $orderId);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIncrementId() : ?string
    {
        return $this->getData(self::INCREMENT_ID);
    }

    /**
     * @param string|null $incrementId
     * @return $this
     */
    public function setIncrementId(?string $incrementId)
    {
        $this->setData(self::INCREMENT_ID, $incrementId);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getQuoteId() : ?int
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * @param int|null $quoteId
     * @return $this
     */
    public function setQuoteId(?int $quoteId)
    {
        $this->setData(self::QUOTE_ID, $quoteId);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMessage() : ?string
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @param string|null $message
     * @return $this
     */
    public function setMessage(?string $message)
    {
        $this->setData(self::MESSAGE, $message);
        return $this;
    }

    /**
     * @return array
     */
    public function getOrderEntry() : array
    {
        return $this->_getEntry(self::ORDER_ENTRY) ?: [];
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setOrderEntry(array $data)
    {
        $this->setData(self::ORDER_ENTRY, $data);
        return $this;
    }

    /**
     * @return array
     */
    public function getBillingEntry() : array
    {
        return $this->_getEntry(self::BILLING_ENTRY) ?: [];
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setBillingEntry(array $data)
    {
        $this->setData(self::BILLING_ENTRY, $data);
        return $this;
    }

    /**
     * @return array
     */
    public function getShippingEntry() : array
    {
        return $this->_getEntry(self::SHIPPING_ENTRY) ?: [];
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setShippingEntry(array $data)
    {
        $this->setData(self::SHIPPING_ENTRY, $data);
        return $this;
    }

    /**
     * @return array
     */
    public function getItemEntry() : array
    {
        return $this->_getEntry(self::ITEM_ENTRY) ?: [];
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setItemEntry(array $data)
    {
        $this->setData(self::ITEM_ENTRY, $data);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCreatedAt() : ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param string|null $createdAt
     * @return $this
     */
    public function setCreatedAt(?string $createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt() : ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param string|null $updatedAt
     * @return $this
     */
    public function setUpdatedAt(?string $updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCollectedAt() : ?string
    {
        return $this->getData(self::COLLECTED_AT);
    }

    /**
     * @param $collectedAt
     * @return $this
     */
    public function setCollectedAt(?string $collectedAt)
    {
        $this->setData(self::COLLECTED_AT, $collectedAt);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getProcessedAt() : ?string
    {
        return $this->getData(self::PROCESSED_AT);
    }

    /**
     * @param string|null $processedAt
     * @return $this
     */
    public function setProcessedAt(?string $processedAt)
    {
        $this->setData(self::PROCESSED_AT, $processedAt);
        return $this;
    }

    /**
     * @param string $entry
     * @return array|array[]
     */
    private function _getEntry(string $entry) : array
    {
        if (!$entry = $this->getData($entry)) {
            return [];
        }

        try {
            $entry = $this->_serializer->unserialize($entry);
        } catch (\InvalidArgumentException $e) {
            $entry = [];
        }

        return is_array($entry) ? $entry : [$entry];
    }
}
