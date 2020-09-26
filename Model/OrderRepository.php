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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use SoftCommerce\Avocado\Api;

/**
 * Class OrderRepository
 * @package SoftCommerce\Avocado\Model
 */
class OrderRepository implements Api\OrderRepositoryInterface
{
    /**
     * @var ResourceModel\Order
     */
    private ResourceModel\Order $_resource;

    /**
     * @var OrderFactory
     */
    private OrderFactory $_entityFactory;

    /**
     * @var ResourceModel\Order\CollectionFactory
     */
    private ResourceModel\Order\CollectionFactory $_collectionFactory;

    /**
     * @var Api\Data\OrderSearchResultsInterfaceFactory
     */
    private Api\Data\OrderSearchResultsInterfaceFactory $_searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private ?CollectionProcessorInterface $_collectionProcessor;

    /**
     * OrderRepository constructor.
     * @param ResourceModel\Order $resource
     * @param OrderFactory $entityFactory
     * @param ResourceModel\Order\CollectionFactory $collectionFactory
     * @param Api\Data\OrderSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ResourceModel\Order $resource,
        OrderFactory $entityFactory,
        ResourceModel\Order\CollectionFactory $collectionFactory,
        Api\Data\OrderSearchResultsInterfaceFactory $searchResultsFactory,
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
     * @return Api\Data\OrderSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ResourceModel\Order\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $this->_collectionProcessor->process($searchCriteria, $collection);

        /** @var Api\Data\OrderInterface $searchResults */
        $searchResult = $this->_searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getPendingRecords()
    {
        return $this->_resource->getPendingRecords();
    }

    /**
     * @return array
     */
    public function getAllIds()
    {
        /** @var ResourceModel\Order\Collection $collection */
        $collection = $this->_collectionFactory->create();
        return $collection->getAllIds();
    }

    /**
     * @param $entityId
     * @param null $field
     * @return Api\Data\OrderInterface|Order
     * @throws NoSuchEntityException
     */
    public function get($entityId, $field = null)
    {
        /** @var Api\Data\OrderInterface $entity */
        $entity = $this->_entityFactory->create();
        $this->_resource->load($entity, $entityId, $field);
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('The entity with ID "%1" doesn\'t exist.', $entityId));
        }

        return $entity;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getLastUpdatedAt()
    {
        return $this->_resource->getLastUpdatedAt();
    }

    /**
     * @param Api\Data\OrderInterface $entity
     * @return Api\Data\OrderInterface
     * @throws CouldNotSaveException
     */
    public function save(Api\Data\OrderInterface $entity)
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
     * @param Api\Data\OrderInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Api\Data\OrderInterface $entity)
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
