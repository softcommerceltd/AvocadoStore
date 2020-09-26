<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\Data\ShipmentInterface as SalesOrderShipmentInterface;
use SoftCommerce\Avocado\Api\Data\OrderInterface;
use SoftCommerce\Avocado\Api\Data\ShipmentInterface;
use SoftCommerce\Avocado\Api\ShipmentCollectManagementInterface;
use SoftCommerce\Avocado\Helper\Data as Helper;
use SoftCommerce\Avocado\Logger\Logger;
use SoftCommerce\Core\Model\Source\Status;

/**
 * Class ShipmentCollectManagement
 * @package SoftCommerce\Avocado\Model
 */
class ShipmentCollectManagement extends AbstractManagement implements ShipmentCollectManagementInterface
{
    /**
     * @var ResourceModel\Shipment
     */
    private ResourceModel\Shipment $_resource;

    /**
     * @return SalesOrderShipmentInterface
     */
    public function getRequest() : SalesOrderShipmentInterface
    {
        return $this->_request;
    }

    /**
     * @param SalesOrderShipmentInterface $shipment
     * @return $this
     */
    public function setRequest(SalesOrderShipmentInterface $shipment)
    {
        $this->_request = $shipment;
        return $this;
    }

    /**
     * ShipmentCollectManagement constructor.
     * @param ResourceModel\Shipment $resource
     * @param Helper $helper
     * @param DateTime $dateTime
     * @param Logger $logger
     * @param Json|null $serializer
     */
    public function __construct(
        ResourceModel\Shipment $resource,
        Helper $helper,
        DateTime $dateTime,
        Logger $logger,
        ?Json $serializer = null
    ) {
        $this->_resource = $resource;
        parent::__construct($helper, $dateTime, $logger, $serializer);
    }

    /**
     * @return $this|ShipmentCollectManagement
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        if (!$this->_helper->getIsActiveShipment()) {
            return $this;
        }

        $existingEntry = $this->_getExistingOrderEntry($this->getRequest()->getOrderId());
        $existingOrderEntityId = $existingEntry[self::ORDER_ENTITY_ID] ?? null;
        $existingShipmentEntityId = $existingEntry[self::SHIPMENT_ENTITY_ID] ?? null;
        if (null === $existingOrderEntityId || null !== $existingShipmentEntityId) {
            return $this;
        }

        try {
            $request = $this->_generateRequest((int) $existingOrderEntityId);
            $this->_submit($request);
            $this->_logger->debug(__METHOD__, [Status::SUCCESS => [$this->getRequest()->getIncrementId() => $request]]);
        } catch (\Exception $e) {
            $this->_logger->debug(__METHOD__, [Status::ERROR => [$this->getRequest()->getIncrementId() => $e->getMessage()]]);
        }

        return $this;
    }

    /**
     * @param $orderId
     * @return array
     * @throws LocalizedException
     */
    private function _getExistingOrderEntry($orderId) : array
    {
        $adapter = $this->_resource->getConnection();
        $select = $adapter->select()
            ->from(
                ['sao_tb' => $adapter->getTableName(OrderInterface::DB_TABLE_NAME)],
                [self::ORDER_ENTITY_ID => OrderInterface::ENTITY_ID]
            )->joinLeft(
                ['sas_tb' => $this->_resource->getMainTable()],
                'sao_tb.entity_id = sas_tb.parent_id',
                [self::SHIPMENT_ENTITY_ID => ShipmentInterface::ENTITY_ID]
            )->where('sao_tb.' . OrderInterface::ORDER_ID . ' = ?', $orderId);

        return current($adapter->fetchAll($select)) ?: [];
    }

    /**
     * @param int $existingOrderEntityId
     * @return array
     */
    private function _generateRequest(int $existingOrderEntityId)
    {
        $trackInfo =
        $serviceInfo =
            [];
        foreach ($this->getRequest()->getTracks() as $item) {
            $item->getTrackNumber() ? $trackInfo[] = $item->getTrackNumber() : null;
            $item->getTitle() ? $serviceInfo[] = $item->getTitle() : null;
        }

        return [
            ShipmentInterface::ENTITY_ID => $this->getRequest()->getEntityId(),
            ShipmentInterface::PARENT_ID => $existingOrderEntityId,
            ShipmentInterface::STATUS => Status::PENDING,
            ShipmentInterface::INCREMENT_ID => $this->getRequest()->getIncrementId(),
            ShipmentInterface::TRACK_NO => implode(', ', $trackInfo ?: []),
            ShipmentInterface::SERVICE_PROVIDER => implode(', ', $serviceInfo ?: []),
            ShipmentInterface::MESSAGE => $this->_serializer->serialize([__('Shipment has been collected. Waiting for submission.')]),
            ShipmentInterface::CREATED_AT => $this->_dateTime->gmtDate()
        ];
    }

    /**
     * @param array $request
     * @return $this
     * @throws LocalizedException
     */
    private function _submit(array $request)
    {
        if (empty($request)) {
            throw new LocalizedException(__('Could not retrieve data for save.'));
        }

        $this->_resource->insertOnDuplicate($request);
        return $this;
    }
}
