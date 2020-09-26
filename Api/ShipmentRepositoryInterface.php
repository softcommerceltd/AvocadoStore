<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use SoftCommerce\Avocado\Model\Shipment;

/**
 * Interface ShipmentRepositoryInterface
 * @package SoftCommerce\Avocado\Api
 */
interface ShipmentRepositoryInterface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\ShipmentSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param $entityId
     * @param null $field
     * @return Data\ShipmentInterface|Shipment
     * @throws NoSuchEntityException
     */
    public function get($entityId, $field = null);

    /**
     * @return array
     */
    public function getAllIds();

    /**
     * @param Data\ShipmentInterface|Shipment $entity
     * @return Data\ShipmentInterface|Shipment
     * @throws CouldNotSaveException
     */
    public function save(Data\ShipmentInterface $entity);

    /**
     * @param array $entries
     * @return int
     * @throws CouldNotSaveException
     */
    public function saveMultiple(array $entries);

    /**
     * @param Data\ShipmentInterface|Shipment $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\ShipmentInterface $entity);

    /**
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($entityId);
}
