<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Shipment;
use SoftCommerce\Avocado\Api\ShipmentCollectManagementInterface;
use SoftCommerce\Avocado\Logger\Logger;
use SoftCommerce\Core\Model\Source\Status;

/**
 * Class SalesOrderShipmentSaveAfter
 * @package SoftCommerce\Avocado\Observer
 */
class SalesOrderShipmentSaveAfter implements ObserverInterface
{
    /**
     * @var ShipmentCollectManagementInterface
     */
    private ShipmentCollectManagementInterface $_shipmentCollectManagement;

    /**
     * @var Logger
     */
    private Logger $_logger;

    /**
     * SalesOrderShipmentSaveAfter constructor.
     * @param ShipmentCollectManagementInterface $shipmentCollectManagement
     * @param Logger $logger
     */
    public function __construct(
        ShipmentCollectManagementInterface $shipmentCollectManagement,
        Logger $logger
    ) {
        $this->_shipmentCollectManagement = $shipmentCollectManagement;
        $this->_logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer): void
    {
        /** @var Shipment $shipment */
        $shipment = $observer->getEvent()->getData('shipment');
        if (!$shipment instanceof Shipment
            || $shipment->getOrigData('entity_id')
        ) {
            return;
        }

        try {
            $this->_shipmentCollectManagement
                ->setRequest($shipment)
                ->execute();
        } catch (\Exception $e) {
            $this->_logger->debug(__METHOD__, [Status::ERROR => [$shipment->getIncrementId() => $e->getMessage()]]);
        }

        return;
    }
}
