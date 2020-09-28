<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Collection;
use Magento\Framework\Phrase;
use Magento\Ui\Component\MassAction\Filter;
use SoftCommerce\Avocado\Api\Data\OrderInterface;
use SoftCommerce\Avocado\Api\OrderCreateManagementInterface;
use SoftCommerce\Avocado\Model\ResourceModel;
use SoftCommerce\Avocado\Model\Source\Status;

/**
 * Class MassCreate
 * @package SoftCommerce\Avocado\Controller\Adminhtml\Order
 */
class MassCreate extends AbstractMassAction implements HttpPostActionInterface
{
    /**
     * @var OrderCreateManagementInterface
     */
    private OrderCreateManagementInterface $_orderCreateManagement;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $_searchCriteriaBuilder;

    /**
     * MassCreate constructor.
     * @param OrderCreateManagementInterface $orderCreateManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Filter $massActionFilter
     * @param ResourceModel\Order $resource
     * @param ResourceModel\Order\CollectionFactory $collectionFactory
     * @param Context $context
     */
    public function __construct(
        OrderCreateManagementInterface $orderCreateManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Filter $massActionFilter,
        ResourceModel\Order $resource,
        ResourceModel\Order\CollectionFactory $collectionFactory,
        Context $context
    ) {
        $this->_orderCreateManagement = $orderCreateManagement;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($massActionFilter, $resource, $collectionFactory, $context);
    }

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
            $this->messageManager->addErrorMessage(__('Could not retrieve request ID(s) data for submission.'));
            return $resultRedirect;
        }

        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(OrderInterface::ENTITY_ID, $ids, 'in')
            ->create();

        $this->_orderCreateManagement
            ->setCanExecuteFlag(true)
            ->setSearchCriteriaRequest($searchCriteria);

        try {
            $this->_orderCreateManagement->execute();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $this->_buildMessageResponseHtml($this->_orderCreateManagement->getResponse());

        return $resultRedirect;
    }

    /**
     * @param $response
     * @return $this
     */
    private function _buildMessageResponseHtml($response)
    {
        if (!is_array($response)) {
            $this->messageManager->addSuccessMessage($response);
            return $this;
        }

        foreach ($response as $status => $message) {
            if (is_array($message)) {
                $this->_buildMessageResponseHtml($message);
                continue;
            }

            if ($message instanceof Phrase) {
                $message = $message->render();
            }

            if ($status === Status::ERROR) {
                $this->messageManager->addErrorMessage($message);
            } elseif ($status === Status::WARNING) {
                $this->messageManager->addWarningMessage($message);
            } elseif ($status === Status::NOTICE) {
                $this->messageManager->addNoticeMessage($message);
            } else {
                $this->messageManager->addSuccessMessage($message);
            }
        }

        return $this;
    }
}
