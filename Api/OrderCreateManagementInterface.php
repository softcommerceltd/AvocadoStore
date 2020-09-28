<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use SoftCommerce\Avocado\Model\OrderCreateManagement;

/**
 * Interface OrderCreateManagementInterface
 * @package SoftCommerce\Avocado\Api
 */
interface OrderCreateManagementInterface
{
    const ORDER_ENTITY = 'order_entity';
    const CLIENT_ORDER_ENTITY = 'client_order_entity';

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
     * @return SearchCriteriaInterface|null
     */
    public function getSearchCriteriaRequest();

    /**
     * @param SearchCriteriaInterface $searchCriteriaRequest
     * @return $this
     */
    public function setSearchCriteriaRequest(SearchCriteriaInterface $searchCriteriaRequest);

    /**
     * @param bool $flag
     * @return $this
     */
    public function setCanExecuteFlag(bool $flag);

    /**
     * @return $this
     * @throws NoSuchEntityException
     */
    public function execute();
}
