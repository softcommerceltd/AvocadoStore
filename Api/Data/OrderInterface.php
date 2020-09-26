<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Api\Data;

/**
 * Interface OrderInterface
 * @package SoftCommerce\Avocado\Api\Data
 */
interface OrderInterface
{
    const DB_TABLE_NAME = 'softcommerce_avocado_order';

    const ID = 'id';
    const ENTITY_ID = 'entity_id';
    const AVOCADO_ORDER_ID = 'avocado_order_id';
    const STATUS = 'status';
    const ORDER_ID = 'order_id';
    const INCREMENT_ID = 'increment_id';
    const QUOTE_ID = 'quote_id';
    const MESSAGE = 'message';
    const ORDER_ENTRY = 'order_entry';
    const BILLING_ENTRY = 'billing_entry';
    const SHIPPING_ENTRY = 'shipping_entry';
    const ITEM_ENTRY = 'item_entry';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const COLLECTED_AT = 'collected_at';
    const PROCESSED_AT = 'processed_at';

    const AVOCADO_BASE_SHIPPING_AMOUNT = 'avocado_base_shipping_amount';
    const AVOCADO_STORE_SHIPPING_AMOUNT = 'avocado_store_shipping_amount';

    /**
     * @return string|null
     */
    public function getAvocadoOrderId() : string;

    /**
     * @param string $orderId
     * @return $this
     */
    public function setAvocadoOrderId(string $orderId);

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
     * @return int|null
     */
    public function getOrderId() : ?int;

    /**
     * @param int|null $orderId
     * @return $this
     */
    public function setOrderId(?int $orderId);

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
     * @return int|null
     */
    public function getQuoteId() : ?int;

    /**
     * @param int|null $quoteId
     * @return $this
     */
    public function setQuoteId(?int $quoteId);

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
     * @return array
     */
    public function getOrderEntry() : array;

    /**
     * @param array $data
     * @return $this
     */
    public function setOrderEntry(array $data);

    /**
     * @return array
     */
    public function getBillingEntry() : array;

    /**
     * @param array $data
     * @return $this
     */
    public function setBillingEntry(array $data);

    /**
     * @return array
     */
    public function getShippingEntry() : array;

    /**
     * @param array $data
     * @return $this
     */
    public function setShippingEntry(array $data);

    /**
     * @return array
     */
    public function getItemEntry() : array;

    /**
     * @param array $data
     * @return $this
     */
    public function setItemEntry(array $data);

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
    public function getUpdatedAt() : ?string;

    /**
     * @param string|null $updatedAt
     * @return $this
     */
    public function setUpdatedAt(?string $updatedAt);

    /**
     * @return string|null
     */
    public function getCollectedAt() : ?string;

    /**
     * @param $collectedAt
     * @return $this
     */
    public function setCollectedAt(?string $collectedAt);

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
