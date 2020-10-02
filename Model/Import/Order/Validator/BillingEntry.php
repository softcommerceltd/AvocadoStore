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
 * Class BillingEntry
 * @package SoftCommerce\Avocado\Model\Import\Order\Validator
 */
class BillingEntry implements ValidatorInterface
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
        if (!isset($rowData[ClientOrderMetadataInterface::RECIPIENT_NAME])) {
            $errors[] = __('Missing required column "%column"', ['column' => ClientOrderMetadataInterface::RECIPIENT_NAME]);
        }
        if (!isset($rowData[ClientOrderMetadataInterface::BILL_ADDRESS_1])) {
            $errors[] = __('Missing required column "%column"', ['column' => ClientOrderMetadataInterface::BILL_ADDRESS_1]);
        }
        if (!isset($rowData[ClientOrderMetadataInterface::BILL_CITY])) {
            $errors[] = __('Missing required column "%column"', ['column' => ClientOrderMetadataInterface::BILL_CITY]);
        }
        if (!isset($rowData[ClientOrderMetadataInterface::BILL_POSTCODE])) {
            $errors[] = __('Missing required column "%column"', ['column' => ClientOrderMetadataInterface::BILL_POSTCODE]);
        }
        if (!isset($rowData[ClientOrderMetadataInterface::BILL_COUNTRY])) {
            $errors[] = __('Missing required column "%column"', ['column' => ClientOrderMetadataInterface::BILL_COUNTRY]);
        }

        return $this->_validationResultFactory->create(['errors' => $errors]);
    }
}

