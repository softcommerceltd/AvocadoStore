<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Model\ResourceModel;

use SoftCommerce\Avocado\Api\Data\ShipmentInterface;

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
}
