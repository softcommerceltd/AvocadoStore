<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace SoftCommerce\Avocado\Model\Source\Import\Behaviour;

use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Source\Import\AbstractBehavior;

/**
 * Class AddNew
 * @package SoftCommerce\Avocado\Model\Source\Import\Behaviour
 */
class AddNew extends AbstractBehavior
{
    /**
     * @return array
     */
    public function toArray()
    {
        return [
            Import::BEHAVIOR_APPEND => __('Add New')
        ];
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'avocado_order';
    }

    /**
     * @param string $entityCode
     * @return array
     */
    public function getNotes($entityCode)
    {
        $messages = [
            'avocado_order' => [
                Import::BEHAVIOR_APPEND => __('Import Avocado Order. Please use semi-column in "Field separator" configuration bellow.')
            ]
        ];
        return $messages[$entityCode] ?? [];
    }
}
