<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);
namespace SoftCommerce\Avocado\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validation\ValidationException;
use SoftCommerce\Avocado\Api\Data\ShipmentInterface;
use SoftCommerce\Avocado\Api\ShipmentRepositoryInterface;
use SoftCommerce\Avocado\Model\ShipmentFactory;

/**
 * InlineEdit Controller
 */
class InlineEdit extends AbstractShipmentList implements HttpPostActionInterface
{
    /**
     * @var DataObjectHelper
     */
    private DataObjectHelper $_dataObjectHelper;

    /**
     * InlineEdit constructor.
     * @param DataObjectHelper $dataObjectHelper
     * @param DataPersistorInterface $dataPersistor
     * @param ShipmentFactory $shipmentFactory
     * @param ShipmentRepositoryInterface $orderRepository
     * @param Context $context
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        DataPersistorInterface $dataPersistor,
        ShipmentFactory $shipmentFactory,
        ShipmentRepositoryInterface $orderRepository,
        Context $context
    ) {
        $this->_dataObjectHelper = $dataObjectHelper;
        parent::__construct($dataPersistor, $shipmentFactory, $orderRepository, $context);
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $errorMessages = [];
        $request = $this->getRequest();
        $requestData = $request->getParam('items', []);

        if ($request->isXmlHttpRequest() && $request->isPost() && $requestData) {
            foreach ($requestData as $itemData) {
                try {
                    $postId = $itemData[ShipmentInterface::ID];
                    $postEntity = $this->_shipmentRepository->get($postId);
                    $this->_dataObjectHelper->populateWithArray($postEntity, $itemData, ShipmentInterface::class);
                    $this->_shipmentRepository->save($postEntity);
                } catch (NoSuchEntityException $e) {
                    $errorMessages[] = __('[ID: %value] Could not retrieve post ID: %1.', $postId);
                } catch (ValidationException $e) {
                    foreach ($e->getErrors() as $localizedError) {
                        $errorMessages[] = __('[ID: %value] %message', [
                            'value' => $postId,
                            'message' => $localizedError->getMessage()
                        ]);
                    }
                } catch (CouldNotSaveException $e) {
                    $errorMessages[] = __(
                        '[ID: %value] %message',
                        [
                            'value' => $postId,
                            'message' => $e->getMessage()
                        ]
                    );
                }
            }
        } else {
            $errorMessages[] = __('Please correct the request data.');
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData([
            'messages' => $errorMessages,
            'error' => count($errorMessages),
        ]);

        return $resultJson;
    }
}
