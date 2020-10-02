<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Api;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface OrderCollectManagementInterface
 * @package SoftCommerce\Avocado\Api
 */
interface OrderCollectManagementInterface
{
    /**
     * @return array
     * @throws LocalizedException
     */
    public function getSource() : array;

    /**
     * @param array $source
     * @return $this
     */
    public function setSource(array $source);

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
     * @return $this
     */
    public function execute();
}
