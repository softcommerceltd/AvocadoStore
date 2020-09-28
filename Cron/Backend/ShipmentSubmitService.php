<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Cron\Backend;

use SoftCommerce\Avocado\Api\ShipmentCreateManagementInterface;
use SoftCommerce\Avocado\Logger\Logger;

/**
 * Class ShipmentSubmitService
 * @package SoftCommerce\Avocado\Cron\Backend
 */
class ShipmentSubmitService
{
    /**
     * @var ShipmentCreateManagementInterface
     */
    private ShipmentCreateManagementInterface $_shipmentCreateManagement;

    /**
     * @var Logger
     */
    private Logger $_logger;

    /**
     * ShipmentSubmitService constructor.
     * @param ShipmentCreateManagementInterface $shipmentCreateManagement
     * @param Logger $logger
     */
    public function __construct(
        ShipmentCreateManagementInterface $shipmentCreateManagement,
        Logger $logger
    ) {
        $this->_shipmentCreateManagement = $shipmentCreateManagement;
        $this->_logger = $logger;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        try {
            $this->_shipmentCreateManagement->execute();
        } catch (\Exception $e) {
            $this->_logger->log(100, $e->getMessage(), [__METHOD__ => $this->_shipmentCreateManagement->getResponse()]);
            return;
        }

        return;
    }
}
