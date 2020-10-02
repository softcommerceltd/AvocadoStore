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
    const ORDER_ID                          = 'order_id';
    const ITEM_ID                           = 'order_item_id';
    const PURCHASE_DATE                     = 'purchase_date';
    const PAYMENT_DATE                      = 'payment_date';
    const BUYER_NAME                        = 'buyer_name';
    const SKU                               = 'sku';
    const PRODUCT_NAME                      = 'product_name';
    const QTY_PURCHASED                     = 'quantity_purchased';
    const CURRENCY                          = 'currency';
    const ITEM_PRICE                        = 'item_price';
    const ITEM_TAX                          = 'item_tax';
    const SHIPPING_PRICE                    = 'shipping_price';
    const SHIPPING_TAX                      = 'shipping_tax';
    const SHIPPING_METHOD                   = 'shipping_service_level';
    const RECIPIENT_NAME                    = 'recipient_name';
    const SHIP_ADDRESS_1                    = 'ship_address_1';
    const SHIP_ADDRESS_2                    = 'ship_address_2';
    const SHIP_CITY                         = 'ship_city';
    const SHIP_POSTCODE                     = 'ship_postal_code';
    const SHIP_COUNTRY                      = 'ship_country';
    const BILL_ADDRESS_1                    = 'bill_address_1';
    const BILL_ADDRESS_2                    = 'bill_address_2';
    const SHIP_ADDRESS_NO                   = 'ship_address_no';
    const BILL_ADDRESS_ADDITIONAL           = 'bill_address_additional';
    const BILL_ADDRESS_STREET               = 'bill_address_street';
    const BILL_CITY                         = 'bill_city';
    const BILL_POSTCODE                     = 'bill_postal_code';
    const BILL_COUNTRY                      = 'bill_country';
    const SHIP_ADDRESS_ADDITIONAL           = 'ship_address_additional';
    const SHIP_ADDRESS_STREET               = 'ship_address_street';

    const BILL_ADDRESS_NO                   = 'bill_address_no';
    const BUYER_ID                          = 'buyer_id';
}
