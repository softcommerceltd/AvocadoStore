<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use SoftCommerce\Avocado\Api\Data\OrderInterface;
use SoftCommerce\Avocado\Api\Data\ShipmentInterface;
use SoftCommerce\Avocado\Model\Source\Status;

/**
 * Class Shipment
 * @package SoftCommerce\Avocado\Model\ResourceModel
 */
class Shipment extends AbstractResource
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init(ShipmentInterface::DB_TABLE_NAME, ShipmentInterface::ENTITY_ID);
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getPendingRecords()
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from(['sao_tb' => $this->getMainTable()])
            ->joinLeft(
                ['sas_tb' => $adapter->getTableName(OrderInterface::DB_TABLE_NAME)],
                'sao_tb.parent_id = sas_tb.entity_id',
                [OrderInterface::ORDER_ID, OrderInterface::AVOCADO_ORDER_ID]
            )->where('sao_tb.' . ShipmentInterface::STATUS . ' = ?', Status::PENDING);

        return $adapter->fetchAssoc($select);
    }
}
