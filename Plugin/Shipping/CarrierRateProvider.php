<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace SoftCommerce\Avocado\Plugin\Shipping;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use SoftCommerce\Avocado\Api\Data\OrderInterface;
use SoftCommerce\Shipping\Model\Carrier\RateProvider;
use SoftCommerce\Shipping\Model\ServiceProvider\Dpd;

/**
 * Class CarrierRateProvider
 * @package SoftCommerce\Avocado\Plugin\Shipping
 */
class CarrierRateProvider
{
    /**
     * @param RateProvider $subject
     * @param Method $result
     * @param array $request
     * @return Method
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterCreateShippingMethod(
        RateProvider $subject,
        $result,
        array $request
    ) {
        $isAvocadoOrder = $subject->getBackendSessionQuote()
            ->getData(OrderInterface::AVOCADO_ORDER_ID);
        $customShippingPrice = $subject->getBackendSessionQuote()
            ->getData(OrderInterface::AVOCADO_BASE_SHIPPING_AMOUNT);

        if (!$isAvocadoOrder || !$customShippingPrice) {
            return $result;
        }

        $result->setPrice($customShippingPrice)
            ->setShippingService(Dpd::SERVICE_NAME);

        return $result;
    }
}
