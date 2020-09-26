<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Api\Data;

use SoftCommerce\Avocado\Model\Shipment;

/**
 * Interface ShipmentInterface
 * @package SoftCommerce\Avocado\Api\Data
 */
interface ShipmentInterface
{
    const DB_TABLE_NAME = 'softcommerce_avocado_shipment';

    const ID = 'id';
    const ENTITY_ID = 'entity_id';
    const PARENT_ID = 'parent_id';
    const STATUS = 'status';
    const INCREMENT_ID = 'increment_id';
    const TRACK_NO = 'track_no';
    const SERVICE_PROVIDER = 'service_provider';
    const MESSAGE = 'message';
    const CREATED_AT = 'created_at';
    const PROCESSED_AT = 'processed_at';

    /**
     * @return int|null
     */
    public function getParentId() : int;

    /**
     * @param int $parentId
     * @return $this
     */
    public function setParentId(int $parentId);

    /**
     * @return string|null
     */
    public function getStatus() : ?string;

    /**
     * @param string|null $status
     * @return $this
     */
    public function setStatus(?string $status);

    /**
     * @return string|null
     */
    public function getIncrementId() : ?string;

    /**
     * @param string|null $incrementId
     * @return $this
     */
    public function setIncrementId(?string $incrementId);

    /**
     * @return string|null
     */
    public function getTrackNo() : ?string;

    /**
     * @param string|null $trackNo
     * @return $this
     */
    public function setTrackNo(?string $trackNo);

    /**
     * @return string|null
     */
    public function getServiceProvider() : ?string;

    /**
     * @param string $serviceProvider
     * @return $this
     */
    public function setServiceProvider(string $serviceProvider);

    /**
     * @return string|null
     */
    public function getMessage() : ?string;

    /**
     * @param string|null $message
     * @return $this
     */
    public function setMessage(?string $message);

    /**
     * @return string|null
     */
    public function getCreatedAt() : ?string;

    /**
     * @param string|null $createdAt
     * @return $this
     */
    public function setCreatedAt(?string $createdAt);

    /**
     * @return string|null
     */
    public function getProcessedAt() : ?string;

    /**
     * @param string|null $processedAt
     * @return $this
     */
    public function setProcessedAt(?string $processedAt);
}
