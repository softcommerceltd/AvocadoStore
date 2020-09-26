<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use SoftCommerce\Avocado\Model\Order;

/**
 * Interface OrderRepositoryInterface
 * @package SoftCommerce\Avocado\Api
 */
interface OrderRepositoryInterface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\OrderSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param $entityId
     * @param null $field
     * @return Data\OrderInterface|Order
     * @throws NoSuchEntityException
     */
    public function get($entityId, $field = null);

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getLastUpdatedAt();

    /**
     * @return array
     */
    public function getPendingRecords();

    /**
     * @return array
     */
    public function getAllIds();

    /**
     * @param Data\OrderInterface $entity
     * @return Data\OrderInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\OrderInterface $entity);

    /**
     * @param array $entries
     * @return int
     * @throws CouldNotSaveException
     */
    public function saveMultiple(array $entries);

    /**
     * @param Data\OrderInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\OrderInterface $entity);

    /**
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($entityId);
}
