<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Ui\Component\Listing\Columns;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\Listing\Columns\Column;
use SoftCommerce\Avocado\Api\Data\OrderInterface;

/**
 * Class OrderId
 * @package SoftCommerce\Avocado\Ui\Component\Listing\Columns
 */
class OrderId extends Column
{
    /**
     * @param array $dataSource
     * @return array
     * @throws NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (!isset($item[$this->getData('name')], $item[OrderInterface::ORDER_ID], $item[OrderInterface::INCREMENT_ID])) {
                    continue;
                }

                $item[$this->getData('name')] = [
                    'edit' => [
                        'href' => $this->context->getUrl(
                            'sales/order/view',
                            ['order_id' => $item[OrderInterface::ORDER_ID]]
                        ),
                        'label' => __('View'),
                        'hidden' => false,
                    ]
                ];
            }
        }

        return $dataSource;
    }
}
