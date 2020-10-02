<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Api;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface ShipmentCreateManagementInterface
 * @package SoftCommerce\Avocado\Api
 */
interface ShipmentCreateManagementInterface
{
    const SOURCE_LOCATION = 'avocado';

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
     * @param int|string|null $key
     * @return array|string|mixed
     */
    public function getRequest($key = null);

    /**
     * @param $value
     * @param null $key
     * @return $this
     */
    public function setRequest($value, $key = null);

    /**
     * @param array|string $data
     * @param null $key
     * @return $this
     */
    public function addRequest($data, $key = null);

    /**
     * @return array|null;
     */
    public function getTargetEntry();

    /**
     * @return $this
     * @throws NoSuchEntityException
     */
    public function execute();
}
