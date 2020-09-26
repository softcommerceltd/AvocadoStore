<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use SoftCommerce\Avocado\Api\Data\OrderInterface;
use SoftCommerce\Core\Model\Source\Status;

/**
 * Class Order
 * @package SoftCommerce\Avocado\Model\ResourceModel
 */
class Order extends AbstractResource
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init(OrderInterface::DB_TABLE_NAME, OrderInterface::ENTITY_ID);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getLastUpdatedAt()
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), [OrderInterface::UPDATED_AT])
            ->order(OrderInterface::UPDATED_AT . ' ' . Select::SQL_DESC);

        return $adapter->fetchOne($select);
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getPendingRecords()
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), [OrderInterface::ENTITY_ID, OrderInterface::AVOCADO_ORDER_ID])
            ->where(OrderInterface::STATUS . ' = ?', Status::PENDING);

        return $adapter->fetchPairs($select);
    }

    /**
     * @param int $salesOrderEntityId
     * @return string
     * @throws LocalizedException
     */
    public function getAvocadoOrderIdByOrderEntityId(int $salesOrderEntityId)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), [OrderInterface::AVOCADO_ORDER_ID])
            ->where(OrderInterface::ORDER_ID . ' = ?', $salesOrderEntityId);

        return $adapter->fetchOne($select);
    }
}
