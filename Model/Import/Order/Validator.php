<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace SoftCommerce\Avocado\Model\Import\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validation\ValidationResultFactory;

/**
 * Class Validator
 * @package SoftCommerce\Avocado\Model\Import\Order
 */
class Validator implements ValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private ValidationResultFactory $_validationResultFactory;

    /**
     * @var ValidatorInterface[]
     */
    private array $_validators;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param array $validators
     * @throws LocalizedException
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        array $validators = []
    ) {
        $this->_validationResultFactory = $validationResultFactory;
        $this->_validators = $validators;
        $this->_initValidators();
    }

    /**
     * @inheritdoc
     */
    public function validate(array $rowData, int $rowNumber)
    {
        $errors = [];
        foreach ($this->_validators as $validator) {
            $validationResult = $validator->validate($rowData, $rowNumber);
            if (!$validationResult->isValid()) {
                $errors[] = $validationResult->getErrors();
            }
        }

        return $this->_validationResultFactory->create(['errors' => array_merge(...$errors ?: [[]])]);
    }

    /**
     * @throws LocalizedException
     */
    private function _initValidators()
    {
        foreach ($this->_validators as $validator) {
            if (!$validator instanceof ValidatorInterface) {
                $this->_validators = [];
                throw new LocalizedException(
                    __('Row Validator must implement %interface.', ['interface' => ValidatorInterface::class])
                );
            }
        }
    }
}
