<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace SoftCommerce\Avocado\Model\Import\Order;

use Magento\Framework\Validation\ValidationResult;

/**
 * Interface ValidatorInterface
 * @package SoftCommerce\Avocado\Model\Import\Order
 */
interface ValidatorInterface
{
    /**
     * @param array $rowData
     * @param int $rowNumber
     * @return ValidationResult
     */
    public function validate(array $rowData, int $rowNumber);
}
