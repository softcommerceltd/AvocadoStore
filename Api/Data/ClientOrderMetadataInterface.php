<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Api\Data;

/**
 * Interface OrderInterface
 * @package SoftCommerce\Avocado\Api\Data
 */
interface ClientOrderMetadataInterface
{
    const ORDER_ID                          = 'order-id';
    const ITEM_ID                           = 'order-item-id';
    const PURCHASE_DATE                     = 'purchase-date';
    const PAYMENT_DATE                      = 'payment-date';
    const BUYER_NAME                        = 'buyer-name';
    const SKU                               = 'sku';
    const PRODUCT_NAME                      = 'product-name';
    const QTY_PURCHASED                     = 'quantity-purchased';
    const CURRENCY                          = 'currency';
    const ITEM_PRICE                        = 'item-price';
    const ITEM_TAX                          = 'item-tax';
    const SHIPPING_PRICE                    = 'shipping-price';
    const SHIPPING_TAX                      = 'shipping-tax';
    const SHIPPING_METHOD                   = 'shipping-service-level';
    const RECIPIENT_NAME                    = 'recipient-name';
    const SHIP_ADDRESS_1                    = 'ship-address-1';
    const SHIP_ADDRESS_2                    = 'ship-address-2';
    const SHIP_CITY                         = 'ship-city';
    const SHIP_POSTCODE                     = 'ship-postal-code';
    const SHIP_COUNTRY                      = 'ship-country';
    const BILL_ADDRESS_1                    = 'bill-address-1';
    const BILL_ADDRESS_2                    = 'bill-address-2';
    const SHIP_ADDRESS_NO                   = 'ship-address-no';
    const BILL_ADDRESS_ADDITIONAL           = 'bill-address-additional';
    const BILL_ADDRESS_STREET               = 'bill-address-street';
    const BILL_CITY                         = 'bill-city';
    const BILL_POSTCODE                     = 'bill-postal-code';
    const BILL_COUNTRY                      = 'bill-country';
    const SHIP_ADDRESS_ADDITIONAL           = 'ship-address-additional';
    const SHIP_ADDRESS_STREET               = 'ship-address-street';

    const BILL_ADDRESS_NO                   = 'bill-address-no';
    const BUYER_ID                          = 'buyer-id';
}
