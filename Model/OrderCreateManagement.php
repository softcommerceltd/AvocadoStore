<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Avocado\Model;

use Magento\Backend\Model\Session\Quote as BackendSessionQuote;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State as AppState;
use Magento\Framework\DataObject;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\Data\OrderInterface as SalesOrderInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Model\Order\Invoice as OrderInvoice;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Store\Model\StoreManagerInterface;
use SoftCommerce\Avocado\Api\Data\ClientOrderMetadataInterface;
use SoftCommerce\Avocado\Api\Data\OrderInterface;
use SoftCommerce\Avocado\Api\OrderCreateManagementInterface;
use SoftCommerce\Avocado\Api\OrderRepositoryInterface;
use SoftCommerce\Avocado\Helper\Data as Helper;
use SoftCommerce\Avocado\Logger\Logger;
use SoftCommerce\Avocado\Model\Source\Status;

/**
 * Class OrderCollectManagement
 * @package SoftCommerce\Avocado\Model
 */
class OrderCreateManagement extends AbstractManagement implements OrderCreateManagementInterface
{
    /**
     * @var AppState
     */
    private AppState $_appState;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $_orderRepository;

    /**
     * @var BackendSessionQuote
     */
    private BackendSessionQuote $_backendSeesionQuote;

    /**
     * @var CheckoutSession
     */
    private CheckoutSession $_checkoutSession;

    /**
     * @var CurrencyFactory
     */
    private CurrencyFactory $_currencyFactory;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $_eventManagement;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $_productRepository;

    /**
     * @var InvoiceService
     */
    private InvoiceService $_invoiceService;

    /**
     * @var QuoteFactory
     */
    private QuoteFactory $_quoteFactory;

    /**
     * @var CartManagementInterface
     */
    private CartManagementInterface $_quoteManagement;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $_quoteRepository;

    /**
     * @var HistoryFactory
     */
    private HistoryFactory $_salesOrderHistoryFactory;

    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    private OrderStatusHistoryRepositoryInterface $_salesOrderHistoryRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $_searchCriteriaBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private FilterGroupBuilder $_filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private FilterBuilder $_filterBuilder;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $_storeManager;

    /**
     * @var TransactionFactory
     */
    private TransactionFactory $_transactionFactory;

    /**
     * @var SalesOrderInterface|null
     */
    private ?SalesOrderInterface $_orderEntity = null;

    /**
     * @var OrderInterface|null
     */
    private ?OrderInterface $_clientOrderEntity = null;

    /**
     * @var array
     */
    private array $_localResponse = [];

    /**
     * @var SearchCriteriaInterface|null
     */
    private ?SearchCriteriaInterface $_searchCriteriaRequest = null;

    /**
     * OrderCreateManagement constructor.
     * @param AppState $appState
     * @param BackendSessionQuote $backendSessionQuote
     * @param CheckoutSession $checkoutSession
     * @param CurrencyFactory $currencyFactory
     * @param ManagerInterface $eventManager
     * @param ProductRepositoryInterface $productRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceService $invoiceService
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param QuoteFactory $quoteFactory
     * @param CartManagementInterface $quoteManagement
     * @param CartRepositoryInterface $quoteRepository
     * @param HistoryFactory $salesOrderHistoryFactory
     * @param OrderStatusHistoryRepositoryInterface $salesOrderHistoryRepository
     * @param StoreManagerInterface $storeManager
     * @param TransactionFactory $transactionFactory
     * @param Helper $helper
     * @param DateTime $dateTime
     * @param Logger $logger
     * @param Json|null $serializer
     */
    public function __construct(
        AppState $appState,
        BackendSessionQuote $backendSessionQuote,
        CheckoutSession $checkoutSession,
        CurrencyFactory $currencyFactory,
        ManagerInterface $eventManager,
        ProductRepositoryInterface $productRepository,
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        QuoteFactory $quoteFactory,
        CartManagementInterface $quoteManagement,
        CartRepositoryInterface $quoteRepository,
        HistoryFactory $salesOrderHistoryFactory,
        OrderStatusHistoryRepositoryInterface $salesOrderHistoryRepository,
        StoreManagerInterface $storeManager,
        TransactionFactory $transactionFactory,
        Helper $helper,
        DateTime $dateTime,
        Logger $logger,
        ?Json $serializer = null
    ) {
        $this->_appState = $appState;
        $this->_backendSeesionQuote = $backendSessionQuote;
        $this->_checkoutSession = $checkoutSession;
        $this->_currencyFactory = $currencyFactory;
        $this->_eventManagement = $eventManager;
        $this->_productRepository = $productRepository;
        $this->_orderRepository = $orderRepository;
        $this->_invoiceService = $invoiceService;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->_quoteFactory = $quoteFactory;
        $this->_quoteManagement = $quoteManagement;
        $this->_quoteRepository = $quoteRepository;
        $this->_salesOrderHistoryFactory = $salesOrderHistoryFactory;
        $this->_salesOrderHistoryRepository = $salesOrderHistoryRepository;
        $this->_storeManager = $storeManager;
        $this->_transactionFactory = $transactionFactory;
        parent::__construct($helper, $dateTime, $logger, $serializer);
    }

    /**
     * @param int|string|null $key
     * @return array|string|mixed
     */
    public function getRequest($key = null)
    {
        return null === $key
            ? ($this->_request ?: [])
            : ($this->_request[$key] ?? []);
    }

    /**
     * @param $value
     * @param null $key
     * @return $this
     */
    public function setRequest($value, $key = null)
    {
        null !== $key
            ? $this->_request[$key] = $value
            : $this->_request = $value;
        return $this;
    }

    /**
     * @return SearchCriteriaInterface|null
     */
    public function getSearchCriteriaRequest()
    {
        return $this->_searchCriteriaRequest;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteriaRequest
     * @return $this
     */
    public function setSearchCriteriaRequest(SearchCriteriaInterface $searchCriteriaRequest)
    {
        $this->_searchCriteriaRequest = $searchCriteriaRequest;
        return $this;
    }

    /**
     * @return OrderCreateManagement
     */
    public function executeBefore()
    {
        $this->_localResponse = [];
        return parent::executeBefore();
    }

    /**
     * @return $this|OrderCreateManagement
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $this->executeBefore();

        if (!$this->_helper->getIsActive()) {
            return $this;
        }

        if (!$searchCriteria = $this->getSearchCriteriaRequest()) {
            $filter = $this->_filterBuilder
                ->setField(OrderInterface::STATUS)
                ->setValue(Status::PENDING)
                ->setConditionType('eq')
                ->create();
            $filterGroup[] = $this->_filterGroupBuilder->setFilters([$filter])->create();

            $filter = $this->_filterBuilder
                ->setField(OrderInterface::ORDER_ID)
                ->setValue(true)
                ->setConditionType('null')
                ->create();
            $filterGroup[] = $this->_filterGroupBuilder->setFilters([$filter])->create();

            $searchCriteria = $this->_searchCriteriaBuilder
                ->setFilterGroups($filterGroup)
                ->create();
        }

        $pageSize = 100;
        $currentPage = 1;
        $searchCriteria
            ->setCurrentPage($currentPage)
            ->setPageSize($pageSize);

        $collection = $this->_orderRepository->getList($searchCriteria);
        if (!$collection->getTotalCount()) {
            $this->addResponse('Orders are up-to-date.', Status::COMPLETE);
            return $this;
        }

        $totalCount = (int) $collection->getTotalCount();
        while ($totalCount > 0) {
            try {
                $this->_processMultiple($collection->getItems());
            } catch (\Exception $e) {
                $this->_localResponse[Status::ERROR] = [__METHOD__ => $e->getMessage()];
            }

            $searchCriteria = $collection->getSearchCriteria()
                ->setCurrentPage(++$currentPage)
                ->setPageSize($pageSize);
            $collection = $this->_orderRepository->getList($searchCriteria);
            $totalCount = $totalCount - $pageSize;
        }

        return $this;
    }

    /**
     * @return OrderCreateManagement
     */
    public function executeAfter()
    {
        $this->setResponse($this->_localResponse);
        $this->_logger->log(100, __METHOD__, $this->getResponse());
        return parent::executeAfter();
    }

    /**
     * @param array $clientOrders
     * @return $this
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    private function _processMultiple(array $clientOrders)
    {
        /** @var OrderInterface $clientOrder */
        foreach ($clientOrders as $clientOrder) {
            try {
                $this->_process($clientOrder);
            } catch (\Exception $e) {
                $this->addResponse([$clientOrder->getAvocadoOrderId() => $e->getMessage()], Status::ERROR);
                $this->_getClientOrder()
                    ->setStatus(Status::ERROR)
                    ->setProcessedAt($this->_dateTime->gmtDate())
                    ->setMessage($this->_serializer->serialize($this->getResponse()));
                $this->_orderRepository->save($this->_getClientOrder());
                $this->_logger->log(100, __METHOD__, [$clientOrder->getAvocadoOrderId() => $e->getMessage()]);
            }
        }

        return $this;
    }

    /**
     * @param OrderInterface $clientOrder
     * @return $this
     */
    private function _processBefore(OrderInterface $clientOrder)
    {
        $this->_error =
        $this->_response =
            [];

        $this->_orderEntity = null;
        $this->_setClientOrder($clientOrder);
        return $this;
    }

    /**
     * @param OrderInterface $clientOrder
     * @return $this
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function _process(OrderInterface $clientOrder)
    {
        $this->_processBefore($clientOrder)
            ->_createOrder()
            ->_createInvoice()
            ->_processAfter();

        return $this;
    }

    /**
     * @return $this
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    private function _processAfter()
    {
        $this->_localResponse = array_merge($this->_localResponse, $this->getResponse());
        $commentHtml = '<b>' . __('Avocado Synchronisation.') . '</b><br />';
        foreach ($this->getResponse() as $index => $data) {
            if (!is_array($data)) {
                continue;
            }

            foreach ($data as $item) {
                if (!$item instanceof Phrase) {
                    continue;
                }
                $commentHtml .= "<i class='{$index}'>{$item->render()}</i><br />";
            }
        }

        $history = $this->_salesOrderHistoryFactory
            ->create()
            ->setParentId($this->_getSalesOrder()->getEntityId())
            ->setStatus($this->_getSalesOrder()->getStatus() ?: Status::PROCESSING)
            ->setComment($commentHtml)
            ->setEntityName('order')
            ->setIsCustomerNotified(false)
            ->setIsVisibleOnFront(false);
        $this->_salesOrderHistoryRepository->save($history);

        if ((array_key_exists(Status::SUCCESS, $this->getResponse()) && array_key_exists(Status::ERROR, $this->getResponse()))
            || array_key_exists(Status::WARNING, $this->getResponse())
        ) {
            $status = Status::WARNING;
        } elseif (array_key_exists(Status::ERROR, $this->getResponse())) {
            $status = Status::ERROR;
        } elseif (array_key_exists(Status::NOTICE, $this->getResponse())
            && !array_key_exists(Status::SUCCESS, $this->getResponse())
        ) {
            $status = Status::NOTICE;
        } else {
            $status = Status::COMPLETE;
        }

        $this->_getClientOrder()
            ->setOrderId($this->_getSalesOrder()->getEntityId())
            ->setStatus($status)
            ->setIncrementId($this->_getSalesOrder()->getIncrementId())
            ->setQuoteId($this->_getSalesOrder()->getQuoteId())
            ->setProcessedAt($this->_dateTime->gmtDate())
            ->setMessage($this->_serializer->serialize($this->getResponse()));
        $this->_orderRepository->save($this->_getClientOrder());

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function _createOrder()
    {
        $clientOrder = $this->_getClientOrder();
        if ($clientOrder->getOrderId()) {
            $this->addResponse(
                __(
                    'Order exists. [Order: %1, Avocado Order: %3]',
                    $this->_getClientOrder()->getOrderId(),
                    $this->_getClientOrder()->getAvocadoOrderId()
                ),
                Status::SUCCESS
            );
            return $this;
        }

        $clientOrderEntry = $clientOrder->getOrderEntry();
        if (!$clientItemEntry = $clientOrder->getItemEntry()) {
            throw new LocalizedException(__('Could not retrieve client order items.'));
        }

        $quote = $this->_quoteFactory->create();
        $store = $this->_storeManager->getStore($this->_helper->getDefaultStore());
        $quote->setStore($store)
            ->setStoreCurrencyCode($clientOrderEntry[ClientOrderMetadataInterface::CURRENCY] ?? 'EUR')
            ->setBaseCurrencyCode($store->getBaseCurrency()->getCode())
            ->setCustomerEmail($this->_helper->getDefaultCustomerEmail())
            ->setCheckoutMethod('guest')
            ->setCustomerIsGuest(true);

        foreach ($clientItemEntry as $item) {
            if (!isset($item[ClientOrderMetadataInterface::QTY_PURCHASED])) {
                continue;
            }

            $request = ['qty' => $item[ClientOrderMetadataInterface::QTY_PURCHASED]];
            $product = $this->_productRepository->get($item[ClientOrderMetadataInterface::SKU]);
            $quote->addProduct($product, new DataObject($request))
                ->setOriginalCustomPrice($item[ClientOrderMetadataInterface::ITEM_PRICE] ?: $product->getPrice());
        }

        $quote
            ->getBillingAddress()
            ->addData($this->_generateBillingAddress());
        $quote
            ->getShippingAddress()
            ->addData($this->_generateShippingAddress());

        $rates = $this->_currencyFactory
            ->create()
            ->getCurrencyRates($quote->getBaseCurrencyCode(), $quote->getStoreCurrencyCode());
        $rates =  current($rates) ?: 0;
        $shippingPrice = $clientOrderEntry[ClientOrderMetadataInterface::SHIPPING_PRICE] ?? 0;
        $baseShippingAmount = floor((float) $shippingPrice / (float) $rates);

        $this->_backendSeesionQuote->setData(
            [
                OrderInterface::AVOCADO_ORDER_ID => $clientOrder->getAvocadoOrderId(),
                OrderInterface::AVOCADO_BASE_SHIPPING_AMOUNT => $baseShippingAmount,
                OrderInterface::AVOCADO_STORE_SHIPPING_AMOUNT => $clientOrderEntry[ClientOrderMetadataInterface::SHIPPING_PRICE] ?? null
            ]
        );

        $quote->getShippingAddress()
            ->setCollectShippingRates(true)
            ->collectShippingRates();

        $rates = $quote->getShippingAddress()->getGroupedAllShippingRates();
        $rates = reset($rates);
        $shippingRates = [];
        /** @var Rate $topRate */
        foreach ($rates as $topRate) {
            $shippingRates[] = $topRate->getData();
        }

        $minCost = min(array_column($shippingRates, 'price'));
        $result = array_search($minCost, array_column($shippingRates, 'price'));
        $shippingRates = isset($shippingRates[$result])
            ? $shippingRates[$result]
            : current($shippingRates);

        if (!isset($shippingRates['code'], $shippingRates['price'], $shippingRates['method_title'])
            || !$shippingCode = $shippingRates['code']
        ) {
            throw new LocalizedException(__('Could not retrieve shipping rates.'));
        }

        $quote
            ->getShippingAddress()
            ->setShippingMethod($shippingCode)
            ->setBaseShippingAmount((float) $baseShippingAmount)
            ->setBaseShippingInclTax((float) $baseShippingAmount)
            ->setShippingAmount((float) $shippingPrice)
            ->setShippingInclTax((float) $shippingPrice)
            ->setShippingDescription($shippingRates['method_title']);

        $this->_quoteRepository->save($quote);

        /** @todo implement purchaseorder for admin only */
        $quote->getPayment()->importData(
            [
                PaymentInterface::KEY_METHOD => \Magento\OfflinePayments\Model\Checkmo::PAYMENT_METHOD_CHECKMO_CODE,
                // PaymentInterface::KEY_PO_NUMBER => $clientOrder->getAvocadoOrderId()
            ]
        );

        $this->_quoteRepository->save($quote);
        $this->_setSalesOrder($this->_quoteManagement->submit($quote));
        $this->_getSalesOrder()->setEmailSent(0);
        $this->_eventManagement->dispatch('sales_order_place_after', ['order' => $this->_getSalesOrder()]);

        $this->addResponse(
            __(
                'Order has been created. [Order: %1, Avocado Order: %2]',
                $this->_getSalesOrder()->getIncrementId(),
                $this->_getClientOrder()->getAvocadoOrderId()
            ),
            Status::SUCCESS
        );

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function _createInvoice()
    {
        if (!$this->_helper->getIsActiveCreateInvoice()
            || !$this->_getSalesOrder()->canInvoice()
        ) {
            $this->addResponse(
                __(
                    'Cannot invoice. [Order: %1,  Client Order: %2]',
                    $this->_getSalesOrder()->getIncrementId(),
                    $this->_getClientOrder()->getAvocadoOrderId()
                ),
                Status::WARNING
            );
            return $this;
        }

        $invoice = $this->_invoiceService->prepareInvoice($this->_getSalesOrder());
        if (!$invoice || !$invoice->getTotalQty()) {
            throw new LocalizedException(__('Could not create invoice.'));
        }

        $invoice->setRequestedCaptureCase(OrderInvoice::CAPTURE_OFFLINE);
        $invoice->setTransactionId(time())
            ->addComment(
                __('Avocado Synchronisation. [Avocado Order: %1]', $this->_getClientOrder()->getAvocadoOrderId()),
                false
            )->setCustomerNoteNotify(false);

        $invoice->register();
        $invoice->getOrder()->setIsInProcess(true);

        $this->_transactionFactory
            ->create()
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();

        $this->addResponse(
            __(
                'Invoice has been created. [Order: %1, Invoice: %2, Avocado Order: %3]',
                $this->_getSalesOrder()->getIncrementId(),
                $invoice->getIncrementId(),
                $this->_getClientOrder()->getAvocadoOrderId()
            ),
            Status::SUCCESS
        );

        return $this;
    }

    /**
     * @return OrderInterface|null
     * @throws LocalizedException
     */
    private function _getClientOrder()
    {
        if (null === $this->_clientOrderEntity) {
            throw new LocalizedException(__('Client order entity is not set.'));
        }
        return $this->_clientOrderEntity;
    }

    /**
     * @param OrderInterface $order
     * @return $this
     */
    private function _setClientOrder(OrderInterface $order)
    {
        $this->_clientOrderEntity = $order;
        return $this;
    }

    /**
     * @return SalesOrderInterface|null
     * @throws LocalizedException
     */
    private function _getSalesOrder()
    {
        if (null === $this->_orderEntity) {
            throw new LocalizedException(__('Order entity is not set.'));
        }
        return $this->_orderEntity;
    }

    /**
     * @param SalesOrderInterface $salesOrder
     * @return $this
     */
    private function _setSalesOrder(SalesOrderInterface $salesOrder)
    {
        $this->_orderEntity = $salesOrder;
        return $this;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    private function _generateBillingAddress()
    {
        $clientShippingEntry = $this->_getClientOrder()->getShippingEntry();
        if (!$clientBillingEntry = $this->_getClientOrder()->getBillingEntry()) {
            throw new LocalizedException(__('Could not retrieve client billing address.'));
        }

        $customerName = explode(' ', $clientBillingEntry[ClientOrderMetadataInterface::RECIPIENT_NAME]
            ?? ($clientShippingEntry[ClientOrderMetadataInterface::RECIPIENT_NAME] ?? ''));

        $customerFirstName = (string) current($customerName);
        array_shift($customerName);

        $customerLastName = is_array($customerName)
            ? implode(' ', $customerName)
            : $customerName;

        $billingStreet = [];
        isset($clientBillingEntry[ClientOrderMetadataInterface::BILL_ADDRESS_1])
            ? $billingStreet[] = $clientBillingEntry[ClientOrderMetadataInterface::BILL_ADDRESS_1]
            : null;
        isset($clientBillingEntry[ClientOrderMetadataInterface::BILL_ADDRESS_2])
            ? $billingStreet[] = $clientBillingEntry[ClientOrderMetadataInterface::BILL_ADDRESS_2]
            : null;

        if (empty($billingStreet)
            && isset($clientBillingEntry[ClientOrderMetadataInterface::BILL_ADDRESS_NO])
            && isset($clientBillingEntry[ClientOrderMetadataInterface::BILL_ADDRESS_STREET])
        ) {
            $billingStreet = [
                $clientBillingEntry[ClientOrderMetadataInterface::BILL_ADDRESS_NO],
                $clientBillingEntry[ClientOrderMetadataInterface::BILL_ADDRESS_STREET]
            ];
        }

        if (!isset($clientBillingEntry[ClientOrderMetadataInterface::BILL_CITY],
                $clientBillingEntry[ClientOrderMetadataInterface::BILL_COUNTRY],
                $clientBillingEntry[ClientOrderMetadataInterface::BILL_POSTCODE])
            || !$billingStreet = trim(implode("\n", $billingStreet ?: []))
        ) {
            throw new LocalizedException(__('Could not retrieve client billing address [street].'));
        }

        return [
            'firstname'             => $customerFirstName,
            'lastname'              => $customerLastName,
            'street'                => $billingStreet,
            'city'                  => $clientBillingEntry[ClientOrderMetadataInterface::BILL_CITY],
            'country_id'            => $clientBillingEntry[ClientOrderMetadataInterface::BILL_COUNTRY],
            'region'                => '',
            'regionId'              => '',
            'postcode'              => $clientBillingEntry[ClientOrderMetadataInterface::BILL_POSTCODE],
            'telephone'             => '0123456789',
            'fax'                   => '',
            'save_in_address_book'  => false
        ];
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    private function _generateShippingAddress()
    {
        $clientBillingEntry = $this->_getClientOrder()->getBillingEntry();
        if (!$clientShippingEntry = $this->_getClientOrder()->getShippingEntry()) {
            throw new LocalizedException(__('Could not retrieve client shipping address.'));
        }

        $customerName = explode(' ', $clientShippingEntry[ClientOrderMetadataInterface::RECIPIENT_NAME]
            ?? ($clientBillingEntry[ClientOrderMetadataInterface::RECIPIENT_NAME] ?? ''));

        $customerFirstName = (string) current($customerName);
        array_shift($customerName);

        $customerLastName = is_array($customerName)
            ? implode(' ', $customerName)
            : $customerName;

        $shippingStreet = [];
        isset($clientShippingEntry[ClientOrderMetadataInterface::SHIP_ADDRESS_1])
            ? $shippingStreet[] = $clientShippingEntry[ClientOrderMetadataInterface::SHIP_ADDRESS_1]
            : null;
        isset($clientShippingEntry[ClientOrderMetadataInterface::SHIP_ADDRESS_2])
            ? $shippingStreet[] = $clientShippingEntry[ClientOrderMetadataInterface::SHIP_ADDRESS_2]
            : null;

        if (empty($shippingStreet)
            && isset($clientShippingEntry[ClientOrderMetadataInterface::SHIP_ADDRESS_NO])
            && isset($clientShippingEntry[ClientOrderMetadataInterface::SHIP_ADDRESS_STREET])
        ) {
            $shippingStreet = [
                $clientShippingEntry[ClientOrderMetadataInterface::SHIP_ADDRESS_NO],
                $clientShippingEntry[ClientOrderMetadataInterface::SHIP_ADDRESS_STREET]
            ];
        }

        if (!isset($clientShippingEntry[ClientOrderMetadataInterface::SHIP_CITY],
                $clientShippingEntry[ClientOrderMetadataInterface::SHIP_COUNTRY],
                $clientShippingEntry[ClientOrderMetadataInterface::SHIP_POSTCODE])
            || !$shippingStreet = trim(implode("\n", $shippingStreet ?: []))
        ) {
            throw new LocalizedException(__('Could not retrieve client billing address [street].'));
        }

        return [
            'firstname'             => $customerFirstName,
            'lastname'              => $customerLastName,
            'street'                => $shippingStreet,
            'city'                  => $clientShippingEntry[ClientOrderMetadataInterface::SHIP_CITY],
            'country_id'            => $clientShippingEntry[ClientOrderMetadataInterface::SHIP_COUNTRY],
            'region'                => '',
            'regionId'              => '',
            'postcode'              => $clientShippingEntry[ClientOrderMetadataInterface::SHIP_POSTCODE],
            'telephone'             => '0123456789',
            'fax'                   => '',
            'save_in_address_book'  => false
        ];
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    private function _getIsAdmin()
    {
        return $this->_appState->getAreaCode() == Area::AREA_ADMINHTML;
    }
}
