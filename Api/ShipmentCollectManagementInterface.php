<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\ShipmentInterface;

/**
 * Interface ShipmentCollectManagementInterface
 * @package SoftCommerce\Avocado\Api
 */
interface ShipmentCollectManagementInterface
{
    const ORDER_ENTITY_ID = 'order_entity_id';
    const SHIPMENT_ENTITY_ID = 'shipment_entity_id';

    /**
     * @param null $key
     * @return array|mixed
     */
    public function getErrors($key = null);

    /**
     * @param int|string|array $data
     * @param int|string|null $key
     * @return $this
     */
    public function setErrors($data, $key = null);

    /**
     * @param null $key
     * @return array|null
     */
    public function getResponse($key = null);

    /**
     * @param int|string|array $data
     * @param int|string|null $key
     * @return $this
     */
    public function setResponse($data, $key = null);

    /**
     * @param array|string $data
     * @param null $key
     * @return $this
     */
    public function addResponse($data, $key = null);

    /**
     * @return ShipmentInterface
     */
    public function getRequest() : ShipmentInterface;

    /**
     * @param ShipmentInterface $shipment
     * @return $this
     */
    public function setRequest(ShipmentInterface $shipment);

    /**
     * @return $this
     * @throws NoSuchEntityException
     */
    public function execute();
}
