<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Controller\Adminhtml\Shipment;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Collection;
use SoftCommerce\Avocado\Api\Data\ShipmentInterface;

/**
 * Class MassStatus
 * @package SoftCommerce\Avocado\Controller\Adminhtml\Shipment
 */
class MassStatus extends AbstractMassAction implements HttpPostActionInterface
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
            $this->messageManager->addErrorMessage(__('Could not retrieve request ID(s) data for status change.'));
            return $resultRedirect;
        }

        if (!$status = $this->getRequest()->getParam('status')) {
            $this->messageManager->addErrorMessage(__('Could not retrieve status request data for change.'));
            return $resultRedirect;
        }

        try {
            $result = $this->_resource->update(
                [ShipmentInterface::STATUS => $status],
                [ShipmentInterface::ENTITY_ID . ' IN (?)' => $ids]
            );
        } catch (\Exception $e) {
            $result = $e->getMessage();
        }

        if (is_string($result)) {
            $this->messageManager->addErrorMessage(
                __('Could not update requested shipments. [ID(s): %1, Error: %2]', $ids, $result)
            );
        } else {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 shipments have been deleted.', is_int($result) ? $result : count($ids))
            );
        }

        return $resultRedirect;
    }
}
