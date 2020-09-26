<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);
namespace SoftCommerce\Avocado\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use SoftCommerce\Avocado\Api\Data\ShipmentInterface;
use SoftCommerce\Avocado\Api\ShipmentRepositoryInterface;
use SoftCommerce\Avocado\Model\Shipment;
use SoftCommerce\Avocado\Model\ShipmentFactory;

/**
 * Class AbstractShipmentList
 * @package SoftCommerce\Avocado\Controller\Adminhtml\Shipment
 */
abstract class AbstractShipmentList extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'SoftCommerce_Avocado::shipment_list';

    /**
     * @var DataPersistorInterface
     */
    protected DataPersistorInterface $_dataPersistor;

    /**
     * @var ShipmentFactory
     */
    protected ShipmentFactory $_shipmentFactory;

    /**
     * @var ShipmentRepositoryInterface
     */
    protected ShipmentRepositoryInterface $_shipmentRepository;

    /**
     * @var ShipmentInterface|Shipment|null
     */
    protected $_shipmentEntity;

    /**
     * AbstractShipmentList constructor.
     * @param DataPersistorInterface $dataPersistor
     * @param ShipmentFactory $shipmentFactory
     * @param ShipmentRepositoryInterface $orderRepository
     * @param Context $context
     */
    public function __construct(
        DataPersistorInterface $dataPersistor,
        ShipmentFactory $shipmentFactory,
        ShipmentRepositoryInterface $orderRepository,
        Context $context
    ) {
        $this->_dataPersistor = $dataPersistor;
        $this->_shipmentFactory = $shipmentFactory;
        $this->_shipmentRepository = $orderRepository;
        parent::__construct($context);
    }

    /**
     * @return ShipmentInterface|Shipment|null
     */
    protected function _initEntity()
    {
        $entityId = $this->getRequest()->getParam(ShipmentInterface::ENTITY_ID)
            ?: ($this->getRequest()->getParam('general')[ShipmentInterface::ENTITY_ID] ?? null);

        if ($entityId) {
            try {
                $this->_shipmentEntity = $this->_shipmentRepository->get($entityId);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Could not find shipment with ID: %1.', $entityId));
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');
                return $this->_shipmentEntity;
            }
        } else {
            $this->_shipmentEntity = $this->_shipmentFactory->create();
        }

        return $this->_shipmentEntity;
    }
}
