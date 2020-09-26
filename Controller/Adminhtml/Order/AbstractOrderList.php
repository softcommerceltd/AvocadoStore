<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);
namespace SoftCommerce\Avocado\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use SoftCommerce\Avocado\Api\Data\OrderInterface;
use SoftCommerce\Avocado\Api\OrderRepositoryInterface;
use SoftCommerce\Avocado\Model\Order;
use SoftCommerce\Avocado\Model\OrderFactory;

/**
 * Class AbstractOrderList
 * @package SoftCommerce\Avocado\Controller\Adminhtml\Order
 */
abstract class AbstractOrderList extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'SoftCommerce_Avocado::order_list';

    /**
     * @var DataPersistorInterface
     */
    protected DataPersistorInterface $_dataPersistor;

    /**
     * @var OrderFactory
     */
    protected OrderFactory $_orderFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $_orderRepository;

    /**
     * @var OrderInterface|Order|null
     */
    protected $_orderEntity;

    /**
     * AbstractOrderList constructor.
     * @param DataPersistorInterface $dataPersistor
     * @param OrderFactory $postFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param Context $context
     */
    public function __construct(
        DataPersistorInterface $dataPersistor,
        OrderFactory $postFactory,
        OrderRepositoryInterface $orderRepository,
        Context $context
    ) {
        $this->_dataPersistor = $dataPersistor;
        $this->_orderFactory = $postFactory;
        $this->_orderRepository = $orderRepository;
        parent::__construct($context);
    }

    /**
     * @return OrderInterface|Order|null
     */
    protected function _initEntity()
    {
        $orderId = $this->getRequest()->getParam(OrderInterface::ENTITY_ID)
            ?: ($this->getRequest()->getParam('general')[OrderInterface::ENTITY_ID] ?? null);

        if ($orderId) {
            try {
                $this->_orderEntity = $this->_orderRepository->get($orderId);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Could not find order with ID: %1.', $orderId));
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');
                return $this->_orderEntity;
            }
        } else {
            $this->_orderEntity = $this->_orderFactory->create();
        }

        return $this->_orderEntity;
    }
}
