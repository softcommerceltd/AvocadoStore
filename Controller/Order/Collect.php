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
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use SoftCommerce\Avocado\Api\OrderCollectManagementInterface;
use SoftCommerce\Avocado\Logger\Logger;
use SoftCommerce\Avocado\Model\Order\FileProcessorInterface;

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
     * @var FileProcessorInterface
     */
    private FileProcessorInterface $_fileProcessor;

    /**
     * @var Logger
     */
    private Logger $_logger;

    /**
     * @var Json|null
     */
    private ?Json $_serializer;

    /**
     * Collect constructor.
     * @param OrderCollectManagementInterface $orderCollectManagement
     * @param FileProcessorInterface $fileProcessor
     * @param Logger $logger
     * @param Context $context
     * @param Json|null $serializer
     */
    public function __construct(
        OrderCollectManagementInterface $orderCollectManagement,
        FileProcessorInterface $fileProcessor,
        Logger $logger,
        Context $context,
        ?Json $serializer = null
    ) {
        $this->_orderCollectManagement = $orderCollectManagement;
        $this->_fileProcessor = $fileProcessor;
        $this->_logger = $logger;
        $this->_serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $request = $this->getRequest()->getContent();
        try {
            $request = $this->_serializer->unserialize($request);
            if (!isset($request['exportUrl'])) {
                return;
            }

            $this->_fileProcessor->downloadSource($request['exportUrl']);
            if (!$sourceData = $this->_fileProcessor->getSourceData()) {
                return;
            }

            $this->_orderCollectManagement
                ->setSource($sourceData)
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
