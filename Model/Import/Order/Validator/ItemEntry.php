<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace SoftCommerce\Avocado\Model\Import\Order\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use SoftCommerce\Avocado\Api\Data\ClientOrderMetadataInterface;
use SoftCommerce\Avocado\Model\Import\Order\ValidatorInterface;

/**
 * Class ItemEntry
 * @package SoftCommerce\Avocado\Model\Import\Order\Validator
 */
class ItemEntry implements ValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private ValidationResultFactory $_validationResultFactory;

    /**
     * ItemEntry constructor.
     * @param ValidationResultFactory $validationResultFactory
     */
    public function __construct(ValidationResultFactory $validationResultFactory)
    {
        $this->_validationResultFactory = $validationResultFactory;
    }

    /**
     * @param array $rowData
     * @param int $rowNumber
     * @return ValidationResult
     */
    public function validate(array $rowData, int $rowNumber)
    {
        $errors = [];
        if (!isset($rowData[ClientOrderMetadataInterface::SKU])) {
            $errors[] = __('Missing required column "%column"', ['column' => ClientOrderMetadataInterface::SKU]);
        }
        if (!isset($rowData[ClientOrderMetadataInterface::QTY_PURCHASED])) {
            $errors[] = __('Missing required column "%column"', ['column' => ClientOrderMetadataInterface::QTY_PURCHASED]);
        }
        if (!isset($rowData[ClientOrderMetadataInterface::ITEM_PRICE])) {
            $errors[] = __('Missing required column "%column"', ['column' => ClientOrderMetadataInterface::ITEM_PRICE]);
        }
        return $this->_validationResultFactory->create(['errors' => $errors]);
    }
}
