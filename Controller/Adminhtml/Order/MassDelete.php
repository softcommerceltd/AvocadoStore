<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);
namespace SoftCommerce\Avocado\Controller\Adminhtml\Order;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Collection;
use SoftCommerce\Avocado\Api\Data\OrderInterface;

/**
 * Class MassDelete
 * @package SoftCommerce\Avocado\Controller\Adminhtml\Order
 */
class MassDelete extends AbstractMassAction implements HttpPostActionInterface
{
    /**
     * @param Collection $collection
     * @return Redirect|mixed
     */
    protected function massAction(Collection $collection)
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        if (!$ids = $collection->getAllIds()) {
            $this->messageManager->addErrorMessage(__('Could not retrieve request ID(s) data for deletion.'));
            return $resultRedirect;
        }

        try {
            $result = $this->_resource->remove([OrderInterface::ENTITY_ID . ' IN (?)' => $ids]);
        } catch (\Exception $e) {
            $result = $e->getMessage();
        }

        if (is_string($result)) {
            $this->messageManager->addErrorMessage(
                __('Could not remove requested posts. [ID(s): %1, Error: %2]', $ids, $result)
            );
        } else {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 posts have been deleted.', is_int($result) ? $result : count($ids))
            );
        }

        return $resultRedirect;
    }
}
