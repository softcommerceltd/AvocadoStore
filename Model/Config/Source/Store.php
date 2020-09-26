<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * Class Store
 * @package SoftCommerce\Avocado\Model\Config\Source
 */
class Store implements OptionSourceInterface
{
    /**
     * @var array
     */
    private array $_options = [];

    /**
     * @var StoreRepositoryInterface
     */
    private StoreRepositoryInterface $_storeRepository;

    /**
     * @var WebsiteRepositoryInterface
     */
    private WebsiteRepositoryInterface $_websiteRepository;

    /**
     * Store constructor.
     * @param StoreRepositoryInterface $storeRepository
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        StoreRepositoryInterface $storeRepository,
        WebsiteRepositoryInterface $websiteRepository
    ) {
        $this->_storeRepository = $storeRepository;
        $this->_websiteRepository = $websiteRepository;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = [];
            foreach ($this->_storeRepository->getList() as $store) {
                if ($store->getCode() == \Magento\Store\Model\Store::ADMIN_CODE) {
                    continue;
                }
                $website = $this->_websiteRepository->getById($store->getWebsiteId());
                $this->_options[] = [
                    'value' => $store->getCode(),
                    'label' => "{$store->getName()} [{$website->getName()}]",
                ];
            }
        }

        return $this->_options;
    }
}
