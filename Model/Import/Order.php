<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Model\Import;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\ImportExport\Helper\Data as ImportHelper;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use SoftCommerce\Avocado\Api\OrderCollectManagementInterface;
use SoftCommerce\Avocado\Model\Import\Order\ValidatorInterface;

/**
 * Class Courses
 */
class Order extends AbstractEntity
{
    const ENTITY_CODE = 'avocado_order';

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resource;

    /**
     * @var OrderCollectManagementInterface
     */
    private OrderCollectManagementInterface $_orderCollectManagement;

    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $_validator;

    /**
     * Order constructor.
     * @param OrderCollectManagementInterface $orderCollectManagement
     * @param ValidatorInterface $validator
     * @param JsonHelper $jsonHelper
     * @param ImportHelper $importExportData
     * @param Data $importData
     * @param ResourceConnection $resource
     * @param Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     */
    public function __construct(
        OrderCollectManagementInterface $orderCollectManagement,
        ValidatorInterface $validator,
        JsonHelper $jsonHelper,
        ImportHelper $importExportData,
        Data $importData,
        ResourceConnection $resource,
        Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator
    ) {
        $this->_orderCollectManagement = $orderCollectManagement;
        $this->_validator = $validator;
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->resource = $resource;
        $this->errorAggregator = $errorAggregator;
    }

    /**
     * @return string
     */
    public function getEntityTypeCode()
    {
        return static::ENTITY_CODE;
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        $result = $this->_validator->validate($rowData, $rowNum);
        if ($result->isValid()) {
            return true;
        }

        foreach ($result->getErrors() as $error) {
            $this->addRowError($error, $rowNum);
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function _importData(): bool
    {
        $request = [];
        while ($batch = $this->_dataSourceModel->getNextBunch()) {
            $request = array_merge($request, $batch);
        }

        if (empty($request)) {
            return false;
        }

        $this->_orderCollectManagement
            ->setSource($request)
            ->execute();

        return true;
    }
}
