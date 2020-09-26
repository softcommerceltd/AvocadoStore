<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class AbstractResource
 * @package SoftCommerce\Avocado\Model\ResourceModel
 */
abstract class AbstractResource extends AbstractDb
{
    /**
     * @param array $data
     * @param array $fields
     * @return int
     * @throws LocalizedException
     */
    public function insertOnDuplicate(array $data, array $fields = [])
    {
        return $this->getConnection()
            ->insertOnDuplicate($this->getMainTable(), $data, $fields);
    }

    /**
     * @param array $bind
     * @param string $where
     * @return $this
     * @throws LocalizedException
     */
    public function update(array $bind, $where = '')
    {
        $this->getConnection()
            ->update($this->getMainTable(), $bind, $where);
        return $this;
    }

    /**
     * @param $where
     * @return int
     * @throws LocalizedException
     */
    public function remove($where)
    {
        return $this->getConnection()
            ->delete($this->getMainTable(), $where);
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function truncateTable()
    {
        if ($this->getConnection()->getTransactionLevel() > 0) {
            $this->getConnection()->delete($this->getMainTable());
        } else {
            $this->getConnection()->truncateTable($this->getMainTable());
        }
        return $this;
    }
}
