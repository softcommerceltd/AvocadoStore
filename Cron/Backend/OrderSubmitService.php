<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Cron\Backend;

use SoftCommerce\Avocado\Api\OrderCreateManagementInterface;
use SoftCommerce\Avocado\Logger\Logger;

/**
 * Class OrderSubmitService
 * @package SoftCommerce\Avocado\Cron\Backend
 */
class OrderSubmitService
{
    /**
     * @var OrderCreateManagementInterface
     */
    private OrderCreateManagementInterface $_orderCreateManagement;

    /**
     * @var Logger
     */
    private Logger $_logger;

    /**
     * OrderSubmitService constructor.
     * @param OrderCreateManagementInterface $orderCreateManagement
     * @param Logger $logger
     */
    public function __construct(
        OrderCreateManagementInterface $orderCreateManagement,
        Logger $logger
    ) {
        $this->_orderCreateManagement = $orderCreateManagement;
        $this->_logger = $logger;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        try {
            $this->_orderCreateManagement->execute();
        } catch (\Exception $e) {
            $this->_logger->log(100, $e->getMessage(), [__METHOD__ => $this->_orderCreateManagement->getResponse()]);
            return;
        }

        return;
    }
}
