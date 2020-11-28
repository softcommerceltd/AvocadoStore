<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Model;

use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use SoftCommerce\Avocado\Helper\Data as Helper;
use SoftCommerce\Avocado\Logger\Logger;

/**
 * Class AbstractManagement
 * @package SoftCommerce\Avocado\Model
 */
abstract class AbstractManagement
{
    /**
     * @var DateTime
     */
    protected DateTime $_dateTime;

    /**
     * @var array
     */
    protected array $_error = [];

    /**
     * @var array
     */
    protected array $_response = [];

    /**
     * @var mixed
     */
    protected $_request;

    /**
     * @var Helper
     */
    protected Helper $_helper;

    /**
     * @var Logger
     */
    protected Logger $_logger;

    /**
     * @var Json|null
     */
    protected ?Json $_serializer;

    /**
     * OrderAbstractManagement constructor.
     * @param Helper $helper
     * @param DateTime $dateTime
     * @param Logger $logger
     * @param Json|null $serializer
     */
    public function __construct(
        Helper $helper,
        DateTime $dateTime,
        Logger $logger,
        ?Json $serializer = null
    ) {
        $this->_helper = $helper;
        $this->_dateTime = $dateTime;
        $this->_logger = $logger;
        $this->_serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * @return $this
     */
    public function executeBefore()
    {
        $this->_error =
        $this->_response =
        $this->_request =
            [];
        return $this;
    }

    /**
     * @return $this
     */
    public function executeAfter()
    {
        return $this;
    }

    /**
     * @param null $key
     * @return array|mixed
     */
    public function getErrors($key = null)
    {
        return null === $key
            ? ($this->_error ?: [])
            : ($this->_error[$key] ?? []);
    }

    /**
     * @param int|string|array $data
     * @param int|string|null $key
     * @return $this
     */
    public function setErrors($data, $key = null)
    {
        null !== $key
            ? $this->_error[$key][] = $data
            : $this->_error[] = $data;
        return $this;
    }

    /**
     * @param null $key
     * @return array|null
     */
    public function getResponse($key = null)
    {
        return null === $key
            ? ($this->_response ?: [])
            : ($this->_response[$key] ?? []);
    }

    /**
     * @param int|string|array $data
     * @param int|string|null $key
     * @return $this
     */
    public function setResponse($data, $key = null)
    {
        null !== $key
            ? $this->_response[$key] = $data
            : $this->_response = $data;
        return $this;
    }

    /**
     * @param array|string $data
     * @param null $key
     * @return $this
     */
    public function addResponse($data, $key = null)
    {
        null !== $key
            ? $this->_response[$key][] = $data
            : $this->_response[] = $data;
        return $this;
    }

    /**
     * @param array|string $data
     * @param null $key
     * @return $this
     */
    public function addRequest($data, $key = null)
    {
        null !== $key && $data
            ? $this->_request[$key][] = $data
            : ($data ? $this->_request[] = $data : null);
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function removeRequest($key)
    {
        if (isset($this->_request[$key])) {
            unset($this->_request[$key]);
        }
        return $this;
    }

    /**
     * @param string $message
     * @param array $data
     * @param bool $force
     * @return $this
     * @throws NoSuchEntityException
     */
    protected function _log(string $message, array $data = [], $force = false)
    {
        if (!$this->_helper->getIsActiveDebug() && false === $force) {
            return $this;
        }

        if ($this->_helper->getIsDebugPrintToArray()) {
            $this->_logger->debug(print_r([$message => $data], true), __METHOD__);
        } else {
            $this->_logger->debug($message, [__METHOD__ => $data]);
        }

        return $this;
    }
}
