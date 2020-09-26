<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace SoftCommerce\Avocado\Ui\DataProvider;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

/**
 * Class OrderListingSearchResult
 * @package SoftCommerce\Avocado\Ui\DataProvider
 */
class OrderListingSearchResult extends SearchResult
{
    /**
     * @return OrderListingSearchResult|void
     */
    protected function _initSelect()
    {
        return parent::_initSelect();
    }
}
