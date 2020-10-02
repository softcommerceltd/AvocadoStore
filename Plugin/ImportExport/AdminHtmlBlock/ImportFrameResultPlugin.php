<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Plugin\ImportExport\AdminHtmlBlock;

use Magento\ImportExport\Block\Adminhtml\Import\Frame\Result;

/**
 * Class ImportFrameResultPlugin
 * @package SoftCommerce\Avocado\Plugin\ImportExport\AdminHtmlBlock
 */
class ImportFrameResultPlugin
{
    /**
     * @param Result $subject
     * @param $message
     * @param bool $appendImportButton
     * @return array
     */
    public function beforeAddSuccess(
        Result $subject,
        $message,
        $appendImportButton = false
    ) {
        $response = $subject->getRequest()->getParam('entity', null);
        if (false !== $appendImportButton
            || null === $response
            || is_array($message)
            || $response != 'avocado_order'
        ) {
            return [$message, $appendImportButton];
        }

        $message .= $this->_getBackUrlBtn($subject);
        return [$message, $appendImportButton];
    }

    /**
     * @param Result $subject
     * @return string
     */
    private function _getBackUrlBtn(Result $subject)
    {
        return '&nbsp;&nbsp;<a class="button action-default scalable" href="'
            . $subject->getUrl('softcommerce_avocado/order/index') . '">' . __('Go Back to Listing') . '</a>';
    }
}
