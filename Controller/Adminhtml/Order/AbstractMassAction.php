<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Collection;
use Magento\Ui\Component\MassAction\Filter;
use SoftCommerce\Avocado\Model\ResourceModel;
use SoftCommerce\Avocado\Model\ResourceModel\Order\CollectionFactory;

/**
 * Class AbstractMassAction
 * @package SoftCommerce\Avocado\Controller\Adminhtml\Order
 */
abstract class AbstractMassAction extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'SoftCommerce_Avocado::order_list';
    const REDIRECT_URL = 'softcommerce_avocado/order/index';

    /**
     * @var Filter
     */
    protected Filter $_massActionFilter;

    /**
     * @var ResourceModel\Order
     */
    protected ResourceModel\Order $_resource;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $_collectionFactory;

    /**
     * AbstractMassAction constructor.
     * @param Filter $massActionFilter
     * @param ResourceModel\Order $resource
     * @param CollectionFactory $collectionFactory
     * @param Context $context
     */
    public function __construct(
        Filter $massActionFilter,
        ResourceModel\Order $resource,
        ResourceModel\Order\CollectionFactory $collectionFactory,
        Context $context
    ) {
        $this->_massActionFilter = $massActionFilter;
        $this->_resource = $resource;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @return Redirect|ResponseInterface|Result\Redirect|ResultInterface|mixed
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::REDIRECT_URL);
        if (!$this->getRequest()->isPost()) {
            $this->messageManager->addErrorMessage(__('Wrong request.'));
            return $resultRedirect;
        }

        try {
            $collection = $this->_massActionFilter
                ->getCollection($this->_collectionFactory->create());
            return $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath(self::REDIRECT_URL);
        }
    }

    /**
     * @param Collection $collection
     * @return mixed
     */
    abstract protected function massAction(Collection $collection);

    /**
     * @return string|null
     */
    protected function getComponentRefererUrl()
    {
        return $this->_massActionFilter->getComponentRefererUrl() ?: self::REDIRECT_URL;
    }
}

