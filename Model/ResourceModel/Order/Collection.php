<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Model\ResourceModel\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SoftCommerce\Avocado\Api\Data\OrderInterface;
use SoftCommerce\Avocado\Model\Order;
use SoftCommerce\Avocado\Model\ResourceModel;

/**
 * Class Collection
 * @package SoftCommerce\Avocado\Model\ResourceModel\Order
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = OrderInterface::ENTITY_ID;

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init(Order::class, ResourceModel\Order::class);
    }
}
