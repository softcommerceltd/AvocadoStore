<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Plugin\Sales\Order;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;
use SoftCommerce\Avocado\Api\Data\OrderInterface;

/**
 * Class SalesOrderGridAvocadoOrderIdProviderPlugin
 * @package SoftCommerce\Avocado\Plugin\Sales\Order
 */
class SalesOrderGridAvocadoOrderIdProviderPlugin
{
    /**
     * @param CollectionFactory $subject
     * @param Collection $collection
     * @param string $requestName
     * @return Collection
     */
    public function afterGetReport($subject, $collection, $requestName)
    {
        if ($requestName !== 'sales_order_grid_data_source' || !$collection instanceof Collection) {
            return $collection;
        }

        try {
            $collection->getSelect()
                ->joinLeft(
                    ['sao_tb' => $collection->getResource()->getTable(OrderInterface::DB_TABLE_NAME)],
                    'sao_tb.' . OrderInterface::ORDER_ID . ' = main_table.entity_id',
                    [OrderInterface::AVOCADO_ORDER_ID]
                );
        } catch (\Zend_Db_Select_Exception $selectException) {
        }

        return $collection;
    }
}
