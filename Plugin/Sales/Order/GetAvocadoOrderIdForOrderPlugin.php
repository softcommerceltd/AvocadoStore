<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace SoftCommerce\Avocado\Plugin\Sales\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use SoftCommerce\Avocado\Model\ResourceModel;

/**
 * Class GetAvocadoOrderIdForOrderPlugin
 * @package SoftCommerce\Avocado\Plugin\Sales\Order
 */
class GetAvocadoOrderIdForOrderPlugin
{
    /**
     * @var OrderExtensionFactory
     */
    private OrderExtensionFactory $_orderExtensionFactory;

    /**
     * @var ResourceModel\Order
     */
    private ResourceModel\Order $_orderResource;

    /**
     * GetAvocadoOrderIdForOrderPlugin constructor.
     * @param OrderExtensionFactory $orderExtensionFactory
     * @param ResourceModel\Order $orderResource
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory,
        ResourceModel\Order $orderResource
    ) {
        $this->_orderExtensionFactory = $orderExtensionFactory;
        $this->_orderResource = $orderResource;
    }

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderInterface $order
     * @return OrderInterface
     * @throws LocalizedException
     */
    public function afterGet(OrderRepositoryInterface $orderRepository, OrderInterface $order): OrderInterface
    {
        $this->setExtensionAttribute($order);
        return $order;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param Collection $resultOrder
     * @return Collection
     * @throws LocalizedException
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        Collection $resultOrder
    ) {
        /** @var $order */
        foreach ($resultOrder->getItems() as $order) {
            $this->afterGet($subject, $order);
        }
        return $resultOrder;
    }

    /**
     * @param OrderInterface $order
     * @return OrderInterface
     * @throws LocalizedException
     */
    private function setExtensionAttribute(OrderInterface $order)
    {
        $extension = $order->getExtensionAttributes();
        if (null === $extension) {
            $extension = $this->_orderExtensionFactory->create();
        }

        if ($extension->getAvocadoOrderId()) {
            return $order;
        }

        if ($avocadoOrderId = $this->_orderResource->getAvocadoOrderIdByOrderEntityId((int) $order->getEntityId())) {
            $extension->setAvocadoOrderId($avocadoOrderId);
        }

        return $order->setExtensionAttributes($extension);
    }
}
