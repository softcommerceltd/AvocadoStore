<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace SoftCommerce\Avocado\Ui\DataProvider;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use SoftCommerce\Avocado\Api\Data\OrderInterface;
use SoftCommerce\Avocado\Api\OrderRepositoryInterface;
use SoftCommerce\Avocado\Model\Order;
use SoftCommerce\Avocado\Model\ResourceModel\Order\Collection;
use SoftCommerce\Avocado\Model\ResourceModel\Order\CollectionFactory;

/**
 * Class PostFormDataProvider
 * @package SoftCommerce\Instagram\Ui\DataProvider
 */
class OrderFormDataProvider extends ModifierPoolDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    private $_dataPersistor;


    private $_orderRepository;

    /**
     * @var array
     */
    private $_loadedData;

    /**
     * @var ImageProcessor
     */
    private $_imageProcessor;

    /**
     * OrderFormDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param OrderRepositoryInterface $productPostRepository
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $pool
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        OrderRepositoryInterface $productPostRepository,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->collection = $collectionFactory->create();
        $this->_dataPersistor = $dataPersistor;
        $this->_orderRepository = $productPostRepository;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getData()
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }

        $items = $this->collection->getItems();
        /** @var OrderInterface|Order $item */
        foreach ($items as $item) {
            $this->_loadedData[$item->getId()]['general'] = $item->getData();
        }

        return $this->_loadedData;
    }
}
