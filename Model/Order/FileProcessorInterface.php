<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Model\Order;

use Magento\Framework\Exception\FileSystemException;

/**
 * Interface FileProcessorInterface
 * @package SoftCommerce\Avocado\Model\Order
 */
interface FileProcessorInterface
{
    const DOWNLOAD_DIR = 'import/avocado/order/';
    const DELIMITER = ';';
    const ENCLOSURE = '"';

    /**
     * @param string $source
     * @return string|null
     * @throws FileSystemException
     */
    public function downloadSource(string $source) : ?string;

    /**
     * @return array
     * @throws \Exception
     */
    public function getSourceData() : array;
}
