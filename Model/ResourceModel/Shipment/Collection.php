<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Model\ResourceModel\Shipment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SoftCommerce\Avocado\Model\Shipment;
use SoftCommerce\Avocado\Model\ResourceModel;

/**
 * Class Collection
 * @package SoftCommerce\Avocado\Model\ResourceModel\Shipment
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init(Shipment::class, ResourceModel\Shipment::class);
    }
}
