<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;

use SoftCommerce\Avocado\Api\Data\OrderInterface;
use SoftCommerce\Avocado\Api\Data\ShipmentInterface;
use SoftCommerce\Avocado\Api\ShipmentCreateManagementInterface;
use SoftCommerce\Avocado\Helper\Data as Helper;
use SoftCommerce\Avocado\Logger\Logger;
use SoftCommerce\Avocado\Model\Source\Status;

/**
 * Class OrderCollectManagement
 * @package SoftCommerce\Avocado\Model
 */
class ShipmentCreateManagement extends AbstractManagement implements ShipmentCreateManagementInterface
{
    /**
     * @var ResourceModel\Shipment
     */
    private ResourceModel\Shipment $_resource;

    /**
     * @var Csv
     */
    private Csv $_csvParser;

    /**
     * @var File
     */
    private File $_file;

    /**
     * @var Filesystem
     */
    private Filesystem $_filesystem;

    /**
     * @var array
     */
    private array $_targetEntry = [];

    /**
     * ShipmentCreateManagement constructor.
     * @param ResourceModel\Shipment $resource
     * @param Csv $csvParser
     * @param Filesystem $filesystem
     * @param File $file
     * @param Helper $helper
     * @param DateTime $dateTime
     * @param Logger $logger
     * @param Json|null $serializer
     */
    public function __construct(
        ResourceModel\Shipment $resource,
        Csv $csvParser,
        Filesystem $filesystem,
        File $file,
        Helper $helper,
        DateTime $dateTime,
        Logger $logger,
        ?Json $serializer = null
    ) {
        $this->_resource = $resource;
        $this->_csvParser = $csvParser;
        $this->_file = $file;
        $this->_filesystem = $filesystem;
        parent::__construct($helper, $dateTime, $logger, $serializer);
    }

    /**
     * @param int|string|null $key
     * @return array|string|mixed
     */
    public function getRequest($key = null)
    {
        return null === $key
            ? ($this->_request ?: [])
            : ($this->_request[$key] ?? []);
    }

    /**
     * @param $value
     * @param null $key
     * @return $this
     */
    public function setRequest($value, $key = null)
    {
        null !== $key
            ? $this->_request[$key] = $value
            : $this->_request = $value;
        return $this;
    }

    /**
     * @return $this|ShipmentCreateManagement
     * @throws LocalizedException
     */
    public function executeBefore()
    {
        $this->_error =
        $this->_request =
        $this->_response =
            [];

        $this->_targetEntry = $this->_resource->getPendingRecords();
        return $this;
    }

    /**
     * @return $this|ShipmentCreateManagement
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        if (!$this->_helper->getIsActiveShipment()) {
            return $this;
        }

        try {
            $this->executeBefore()
                ->_process();
        } catch (\Exception $e) {
            $this->setResponse([__METHOD__ => $e->getMessage()], Status::ERROR);
        }

        $this->executeAfter();

        return $this;
    }

    /**
     * @return $this|ShipmentCreateManagement
     * @throws LocalizedException
     */
    public function executeAfter()
    {
        $status = array_key_exists(Status::ERROR, $this->getResponse())
            ? Status::ERROR
            : Status::COMPLETE;

        if (!$ids = array_keys($this->_getTargetEntry())) {
            return $this;
        }

        $this->_resource->update(
            [
                ShipmentInterface::STATUS => $status,
                ShipmentInterface::PROCESSED_AT => $this->_dateTime->gmtDate(),
                ShipmentInterface::MESSAGE => $this->_serializer->serialize(
                    $status === Status::ERROR ? $this->getResponse()
                        : [Status::SUCCESS => 'Shipments have been processed']
                )
            ],
            [ShipmentInterface::ENTITY_ID . ' IN (?)' => $ids]
        );

        $this->setResponse(
            [
                Status::SUCCESS => sprintf('A total of %s shipments have been processed.', count($ids))
            ]
        );

        $this->_logger->log(100, __METHOD__, $this->getResponse());
        return $this;
    }

    /**
     * @return $this
     * @throws FileSystemException
     * @throws LocalizedException
     */
    private function _process()
    {
        $this->_generate()
            ->_submit();

        return $this;
    }

    /**
     * @return $this
     */
    private function _generate()
    {
        if (empty($this->_getTargetEntry())) {
            return $this;
        }

        $this->addRequest(
            ['order', 'tracking_id', 'package_company']
        );

        foreach ($this->_getTargetEntry() as $item) {
            if (!isset($item[OrderInterface::AVOCADO_ORDER_ID],
                $item[ShipmentInterface::TRACK_NO],
                $item[ShipmentInterface::SERVICE_PROVIDER])
            ) {
                continue;
            }

            $this->addRequest(
                [
                    'order' => $item[OrderInterface::AVOCADO_ORDER_ID],
                    'tracking_id' => $item[ShipmentInterface::TRACK_NO],
                    'package_company' => $item[ShipmentInterface::SERVICE_PROVIDER]
                ]
            );
        }

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     * @throws FileSystemException
     */
    private function _submit()
    {
        if (empty($this->getRequest())) {
            return $this;
        }

        $varDir = $this->_filesystem->getDirectoryWrite(DirectoryList::PUB);
        $sourceDir = $varDir->getAbsolutePath(self::SOURCE_LOCATION);

        $this->_file
            ->setAllowCreateFolders(true)
            ->checkAndCreateFolder($sourceDir);

        $this->_csvParser
            ->setEnclosure('"')
            ->setDelimiter(',')
            ->appendData($sourceDir . '/shipment.csv', $this->getRequest());

        return $this;
    }

    /**
     * @return array|null;
     */
    private function _getTargetEntry()
    {
        return $this->_targetEntry;
    }
}
