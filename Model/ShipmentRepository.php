<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use SoftCommerce\Avocado\Api;

/**
 * Class ShipmentRepository
 * @package SoftCommerce\Avocado\Model
 */
class ShipmentRepository implements Api\ShipmentRepositoryInterface
{
    /**
     * @var ResourceModel\Shipment
     */
    private ResourceModel\Shipment $_resource;

    /**
     * @var ShipmentFactory
     */
    private ShipmentFactory $_entityFactory;

    /**
     * @var ResourceModel\Shipment\CollectionFactory
     */
    private ResourceModel\Shipment\CollectionFactory $_collectionFactory;

    /**
     * @var Api\Data\ShipmentSearchResultsInterfaceFactory
     */
    private Api\Data\ShipmentSearchResultsInterfaceFactory $_searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private ?CollectionProcessorInterface $_collectionProcessor;

    /**
     * ShipmentRepository constructor.
     * @param ResourceModel\Shipment $resource
     * @param ShipmentFactory $entityFactory
     * @param ResourceModel\Shipment\CollectionFactory $collectionFactory
     * @param Api\Data\ShipmentSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ResourceModel\Shipment $resource,
        ShipmentFactory $entityFactory,
        ResourceModel\Shipment\CollectionFactory $collectionFactory,
        Api\Data\ShipmentSearchResultsInterfaceFactory $searchResultsFactory,
        ?CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->_resource = $resource;
        $this->_entityFactory = $entityFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_searchResultsFactory = $searchResultsFactory;
        $this->_collectionProcessor = $collectionProcessor
            ?: ObjectManager::getInstance()->get(CollectionProcessorInterface::class);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Api\Data\ShipmentSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ResourceModel\Shipment\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $this->_collectionProcessor->process($searchCriteria, $collection);

        /** @var Api\Data\ShipmentInterface $searchResults */
        $searchResult = $this->_searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @return array
     */
    public function getAllIds()
    {
        /** @var ResourceModel\Shipment\Collection $collection */
        $collection = $this->_collectionFactory->create();
        return $collection->getAllIds();
    }

    /**
     * @param $entityId
     * @param null $field
     * @return Api\Data\ShipmentInterface|Shipment
     * @throws NoSuchEntityException
     */
    public function get($entityId, $field = null)
    {
        /** @var Api\Data\ShipmentInterface $entity */
        $entity = $this->_entityFactory->create();
        $this->_resource->load($entity, $entityId, $field);
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('The entity with ID "%1" doesn\'t exist.', $entityId));
        }

        return $entity;
    }

    /**
     * @param Api\Data\ShipmentInterface|Shipment $entity
     * @return Api\Data\ShipmentInterface|Shipment
     * @throws CouldNotSaveException
     */
    public function save(Api\Data\ShipmentInterface $entity)
    {
        try {
            $this->_resource->save($entity);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $entity;
    }

    /**
     * @param array $entries
     * @return int
     * @throws CouldNotSaveException
     */
    public function saveMultiple(array $entries)
    {
        try {
            $result = $this->_resource->insertOnDuplicate($entries);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $result;
    }

    /**
     * @param Api\Data\ShipmentInterface|Shipment $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Api\Data\ShipmentInterface $entity)
    {
        try {
            $this->_resource->delete($entity);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($entityId)
    {
        return $this->delete($this->get($entityId));
    }
}
