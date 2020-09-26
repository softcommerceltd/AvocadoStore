<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use SoftCommerce\Avocado\Api\Data\ShipmentInterface;
use SoftCommerce\Avocado\Model\ResourceModel\Shipment\Collection;

/**
 * @method ResourceModel\Shipment getResource()
 * @method Collection getCollection()
 */
class Shipment extends AbstractModel implements
    ShipmentInterface,
    IdentityInterface
{
    const CACHE_TAG = 'softcommerce_avocado_shipment';
    protected $_cacheTag = 'softcommerce_avocado_shipment';
    protected $_eventPrefix = 'softcommerce_avocado_shipment';

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Shipment::class);
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return int|null
     */
    public function getParentId() : int
    {
        return $this->getData(self::PARENT_ID);
    }

    /**
     * @param int $parentId
     * @return $this
     */
    public function setParentId(int $parentId)
    {
        $this->setData(self::PARENT_ID, $parentId);
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
     * @return string|null
     */
    public function getTrackNo() : ?string
    {
        return $this->getData(self::TRACK_NO);
    }

    /**
     * @param string|null $trackNo
     * @return $this
     */
    public function setTrackNo(?string $trackNo)
    {
        $this->setData(self::TRACK_NO, $trackNo);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getServiceProvider() : ?string
    {
        return $this->getData(self::SERVICE_PROVIDER);
    }

    /**
     * @param string $serviceProvider
     * @return $this
     */
    public function setServiceProvider(string $serviceProvider)
    {
        $this->setData(self::SERVICE_PROVIDER, $serviceProvider);
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
}

