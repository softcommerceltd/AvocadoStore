<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class OrderId
 * @package SoftCommerce\Avocado\Ui\Component\Listing\Columns
 */
class OrderId extends Column
{
    /**
     * @inheritDoc
     */
    protected function applySorting()
    {
        $sorting = $this->getContext()->getRequestParam('sorting');
        $isSortable = $this->getData('config/sortable');
        if ($isSortable !== false
            && !empty($sorting['field'])
            && !empty($sorting['direction'])
            && $sorting['field'] === $this->getName()
        ) {
            $collection = $this->getContext()->getDataProvider()->getCollection();
            $collection->joinField(
                'attribute_set',
                'eav_attribute_set',
                'attribute_set_name',
                'attribute_set_id=attribute_set_id',
                null,
                'left'
            );
            $collection->getSelect()->order('attribute_set_name ' . $sorting['direction']);
        }
    }
}
