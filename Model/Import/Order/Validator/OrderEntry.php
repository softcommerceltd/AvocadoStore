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
 * Class OrderEntry
 * @package SoftCommerce\Avocado\Model\Import\Order\Validator
 */
class OrderEntry implements ValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private ValidationResultFactory $_validationResultFactory;

    /**
     * BillingEntry constructor.
     * @param ValidationResultFactory $validationResultFactory
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory
    ) {
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
        if (!isset($rowData[ClientOrderMetadataInterface::ORDER_ID])) {
            $errors[] = __('Missing required column "%column"', ['column' => ClientOrderMetadataInterface::ORDER_ID]);
        }
        if (!isset($rowData[ClientOrderMetadataInterface::BUYER_NAME])) {
            $errors[] = __('Missing required column "%column"', ['column' => ClientOrderMetadataInterface::BUYER_NAME]);
        }
        if (!isset($rowData[ClientOrderMetadataInterface::CURRENCY])) {
            $errors[] = __('Missing required column "%column"', ['column' => ClientOrderMetadataInterface::CURRENCY]);
        }
        if (!isset($rowData[ClientOrderMetadataInterface::SHIPPING_PRICE])) {
            $errors[] = __('Missing required column "%column"', ['column' => ClientOrderMetadataInterface::SHIPPING_PRICE]);
        }

        return $this->_validationResultFactory->create(['errors' => $errors]);
    }
}
