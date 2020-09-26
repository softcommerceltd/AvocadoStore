<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use SoftCommerce\Avocado\Api\OrderCollectManagementInterface;
use SoftCommerce\Avocado\Logger\Logger;

/**
 * Class Collect
 * @package SoftCommerce\Avocado\Controller\Order
 */
class Collect extends Action implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var OrderCollectManagementInterface
     */
    private OrderCollectManagementInterface $_orderCollectManagement;

    /**
     * @var Logger
     */
    private Logger $_logger;

    /**
     * Collect constructor.
     * @param OrderCollectManagementInterface $orderCollectManagement
     * @param Logger $logger
     * @param Context $context
     */
    public function __construct(
        OrderCollectManagementInterface $orderCollectManagement,
        Logger $logger,
        Context $context
    ) {
        $this->_orderCollectManagement = $orderCollectManagement;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        if (!$request = $this->getRequest()->getContent()) {
            return;
        }

        try {
            $this->_orderCollectManagement
                ->setSource($request)
                ->execute();
        } catch (\Exception $e) {
            $this->_logger->log(100, __METHOD__, ['error' => $e->getMessage(), 'request' => $request]);
        }

        return;
    }

    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
