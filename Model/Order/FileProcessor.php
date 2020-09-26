<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Avocado\Model\Order;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Stdlib\DateTime\DateTime;
use SoftCommerce\Avocado\Logger\Logger;

/**
 * Class FileProcessor
 * @package SoftCommerce\Avocado\Model\Order
 */
class FileProcessor implements FileProcessorInterface
{
    /**
     * @var string|null
     */
    private ?string $_source = null;

    /**
     * @var Csv
     */
    private Csv $_csvParser;

    /**
     * @var DateTime
     */
    private DateTime $_dateTime;

    /**
     * @var File
     */
    private File $_file;

    /**
     * @var Filesystem
     */
    private Filesystem $_filesystem;

    /**
     * @var Logger
     */
    private Logger $_logger;

    /**
     * FileProcessor constructor.
     * @param Csv $csvParser
     * @param DateTime $dateTime
     * @param Filesystem $filesystem
     * @param File $file
     * @param Logger $logger
     */
    public function __construct(
        Csv $csvParser,
        DateTime $dateTime,
        Filesystem $filesystem,
        File $file,
        Logger $logger
    ) {
        $this->_csvParser = $csvParser;
        $this->_dateTime = $dateTime;
        $this->_file = $file;
        $this->_filesystem = $filesystem;
        $this->_logger = $logger;
    }

    /**
     * @param string $source
     * @return string|null
     * @throws FileSystemException
     */
    public function downloadSource(string $source) : ?string
    {
        $options = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];
        $context = stream_context_create($options);
        $contents = @file_get_contents($source, false, $context);

        $varDir = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $downloadPath = $varDir->getAbsolutePath(self::DOWNLOAD_DIR);
        $this->_source = $downloadPath . '/' . $this->_dateTime->date('d-m-Y_H-i-s') . '.csv';

        $this->_file
            ->setAllowCreateFolders(true)
            ->open(['path' => $downloadPath]);
        $this->_file->write($this->_source, $contents, 0666);

        return $this->_source;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getSourceData() : array
    {
        if (!$this->_source) {
            return [];
        }

        $this->_csvParser
            ->setDelimiter(self::DELIMITER)
            ->setEnclosure(self::ENCLOSURE);
        if (!$data = $this->_csvParser->getData($this->_source)) {
            return [];
        }

        array_walk($data, function (&$a) use ($data) {
            $a = array_combine($data[0], $a);
        });
        array_shift($data);

        return $data ?: [];
    }
}
