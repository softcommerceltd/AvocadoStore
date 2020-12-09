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
use Magento\OfflinePayments\Model\Checkmo;
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
    private AppState $appState;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var BackendSessionQuote
     */
    private BackendSessionQuote $backendSeesionQuote;

    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     * @var CurrencyFactory
     */
    private CurrencyFactory $currencyFactory;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $eventManagement;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var InvoiceService
     */
    private InvoiceService $invoiceService;

    /**
     * @var QuoteFactory
     */
    private QuoteFactory $quoteFactory;

    /**
     * @var CartManagementInterface
     */
    private CartManagementInterface $quoteManagement;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $quoteRepository;

    /**
     * @var HistoryFactory
     */
    private HistoryFactory $salesOrderHistoryFactory;

    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    private OrderStatusHistoryRepositoryInterface $salesOrderHistoryRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private FilterGroupBuilder $filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private FilterBuilder $filterBuilder;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var TransactionFactory
     */
    private TransactionFactory $transactionFactory;

    /**
     * @var SalesOrderInterface|null
     */
    private ?SalesOrderInterface $orderEntity = null;

    /**
     * @var OrderInterface|null
     */
    private ?OrderInterface $clientOrderEntity = null;

    /**
     * @var array
     */
    private array $localResponse = [];

    /**
     * @var SearchCriteriaInterface|null
     */
    private ?SearchCriteriaInterface $searchCriteriaRequest = null;

    /**
     * @var bool
     */
    private bool $canExecuteFlag = false;

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
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param FilterBuilder $filterBuilder
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
        $this->appState = $appState;
        $this->backendSeesionQuote = $backendSessionQuote;
        $this->checkoutSession = $checkoutSession;
        $this->currencyFactory = $currencyFactory;
        $this->eventManagement = $eventManager;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->quoteFactory = $quoteFactory;
        $this->quoteManagement = $quoteManagement;
        $this->quoteRepository = $quoteRepository;
        $this->salesOrderHistoryFactory = $salesOrderHistoryFactory;
        $this->salesOrderHistoryRepository = $salesOrderHistoryRepository;
        $this->storeManager = $storeManager;
        $this->transactionFactory = $transactionFactory;
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
        return $this->searchCriteriaRequest;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteriaRequest
     * @return $this
     */
    public function setSearchCriteriaRequest(SearchCriteriaInterface $searchCriteriaRequest)
    {
        $this->searchCriteriaRequest = $searchCriteriaRequest;
        return $this;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setCanExecuteFlag(bool $flag)
    {
        $this->canExecuteFlag = $flag;
        return $this;
    }

    /**
     * @return OrderCreateManagement
     */
    public function executeBefore()
    {
        $this->localResponse = [];
        return parent::executeBefore();
    }

    /**
     * @return $this|OrderCreateManagement
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $this->executeBefore();

        if (!$this->canExecute()) {
            return $this;
        }

        if (!$searchCriteria = $this->getSearchCriteriaRequest()) {
            $filter = $this->filterBuilder
                ->setField(OrderInterface::STATUS)
                ->setValue(Status::PENDING)
                ->setConditionType('eq')
                ->create();
            $filterGroup[] = $this->filterGroupBuilder->setFilters([$filter])->create();

            $filter = $this->filterBuilder
                ->setField(OrderInterface::ORDER_ID)
                ->setValue(true)
                ->setConditionType('null')
                ->create();
            $filterGroup[] = $this->filterGroupBuilder->setFilters([$filter])->create();

            $searchCriteria = $this->searchCriteriaBuilder
                ->setFilterGroups($filterGroup)
                ->create();
        }

        $pageSize = 100;
        $currentPage = 1;
        $searchCriteria
            ->setCurrentPage($currentPage)
            ->setPageSize($pageSize);

        $collection = $this->orderRepository->getList($searchCriteria);
        if (!$collection->getTotalCount()) {
            $this->addResponse('Orders are up-to-date.', Status::COMPLETE);
            return $this;
        }

        $totalCount = (int) $collection->getTotalCount();
        while ($totalCount > 0) {
            try {
                $this->processMultiple($collection->getItems());
            } catch (\Exception $e) {
                $this->localResponse[Status::ERROR] = [__METHOD__ => $e->getMessage()];
            }

            $searchCriteria = $collection->getSearchCriteria()
                ->setCurrentPage(++$currentPage)
                ->setPageSize($pageSize);
            $collection = $this->orderRepository->getList($searchCriteria);
            $totalCount = $totalCount - $pageSize;
        }

        return $this;
    }

    /**
     * @return OrderCreateManagement
     */
    public function executeAfter()
    {
        $this->setResponse($this->localResponse);
        $this->_logger->log(100, __METHOD__, $this->getResponse());
        return parent::executeAfter();
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    private function canExecute()
    {
        if (false !== $this->canExecuteFlag) {
            return $this->canExecuteFlag;
        }

        return $this->_helper->getIsActive();
    }

    /**
     * @param array $clientOrders
     * @return $this
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    private function processMultiple(array $clientOrders)
    {
        /** @var OrderInterface $clientOrder */
        foreach ($clientOrders as $clientOrder) {
            try {
                $this->process($clientOrder);
            } catch (\Exception $e) {
                $this->addResponse([$clientOrder->getAvocadoOrderId() => $e->getMessage()], Status::ERROR);
                $this->getClientOrder()
                    ->setStatus(Status::ERROR)
                    ->setProcessedAt($this->_dateTime->gmtDate())
                    ->setMessage($this->_serializer->serialize($this->getResponse()));
                $this->orderRepository->save($this->getClientOrder());
                $this->_logger->log(100, __METHOD__, [$clientOrder->getAvocadoOrderId() => $e->getMessage()]);
            }
        }

        return $this;
    }

    /**
     * @param OrderInterface $clientOrder
     * @return $this
     */
    private function processBefore(OrderInterface $clientOrder)
    {
        $this->_error =
        $this->_response =
            [];

        $this->orderEntity = null;
        $this->setClientOrder($clientOrder);
        return $this;
    }

    /**
     * @param OrderInterface $clientOrder
     * @return $this
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function process(OrderInterface $clientOrder)
    {
        $this->processBefore($clientOrder)
            ->createOrder()
            ->createInvoice()
            ->processAfter();

        return $this;
    }

    /**
     * @return $this
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    private function processAfter()
    {
        if (null === $this->orderEntity) {
            return $this;
        }

        $this->localResponse = array_merge($this->localResponse, $this->getResponse());
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

        $history = $this->salesOrderHistoryFactory
            ->create()
            ->setParentId($this->getSalesOrder()->getEntityId())
            ->setStatus($this->getSalesOrder()->getStatus() ?: Status::PROCESSING)
            ->setComment($commentHtml)
            ->setEntityName('order')
            ->setIsCustomerNotified(false)
            ->setIsVisibleOnFront(false);
        $this->salesOrderHistoryRepository->save($history);

        if ((array_key_exists(Status::SUCCESS, $this->getResponse())
                && array_key_exists(Status::ERROR, $this->getResponse()))
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

        $this->getClientOrder()
            ->setOrderId($this->getSalesOrder()->getEntityId())
            ->setStatus($status)
            ->setIncrementId($this->getSalesOrder()->getIncrementId())
            ->setQuoteId($this->getSalesOrder()->getQuoteId())
            ->setProcessedAt($this->_dateTime->gmtDate())
            ->setMessage($this->_serializer->serialize($this->getResponse()));
        $this->orderRepository->save($this->getClientOrder());

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function createOrder()
    {
        $clientOrder = $this->getClientOrder();
        if ($clientOrder->getOrderId()) {
            $this->addResponse(
                __(
                    'Order exists. [Order: %1, Avocado Order: %3]',
                    $this->getClientOrder()->getOrderId(),
                    $this->getClientOrder()->getAvocadoOrderId()
                ),
                Status::SUCCESS
            );
            return $this;
        }

        $clientOrderEntry = $clientOrder->getOrderEntry();
        if (!$clientItemEntry = $clientOrder->getItemEntry()) {
            throw new LocalizedException(__('Could not retrieve client order items.'));
        }

        $quote = $this->quoteFactory->create();
        $store = $this->storeManager->getStore($this->_helper->getDefaultStore());
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
            $product = $this->productRepository->get($item[ClientOrderMetadataInterface::SKU]);
            $quote->addProduct($product, new DataObject($request))
                ->setOriginalCustomPrice($item[ClientOrderMetadataInterface::ITEM_PRICE] ?: $product->getPrice());
        }

        $quote
            ->getBillingAddress()
            ->addData($this->generateBillingAddress());
        $quote
            ->getShippingAddress()
            ->addData($this->generateShippingAddress());

        $rates = $this->currencyFactory
            ->create()
            ->getCurrencyRates($quote->getBaseCurrencyCode(), $quote->getStoreCurrencyCode());
        $rates =  current($rates) ?: 0;
        $shippingPrice = $clientOrderEntry[ClientOrderMetadataInterface::SHIPPING_PRICE] ?? 0;
        $baseShippingAmount = floor((float) $shippingPrice / (float) $rates);

        $this->backendSeesionQuote->setData(
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
        $shippingRates = $shippingRates[$result] ?? current($shippingRates);

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

        $this->quoteRepository->save($quote);

        /** @todo implement purchaseorder for admin only */
        $quote->getPayment()->importData(
            [
                PaymentInterface::KEY_METHOD => Checkmo::PAYMENT_METHOD_CHECKMO_CODE,
                // PaymentInterface::KEY_PO_NUMBER => $clientOrder->getAvocadoOrderId()
            ]
        );

        $this->quoteRepository->save($quote);
        $this->setSalesOrder($this->quoteManagement->submit($quote));
        $this->getSalesOrder()->setEmailSent(0);
        $this->eventManagement->dispatch('sales_order_place_after', ['order' => $this->getSalesOrder()]);

        $this->addResponse(
            __(
                'Order has been created. [Order: %1, Avocado Order: %2]',
                $this->getSalesOrder()->getIncrementId(),
                $this->getClientOrder()->getAvocadoOrderId()
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
    private function createInvoice()
    {
        if (!$this->_helper->getIsActiveCreateInvoice()
            || null === $this->orderEntity
            || !$this->getSalesOrder()->canInvoice()
        ) {
            $this->addResponse(
                __(
                    'Cannot invoice. [Order: %1,  Client Order: %2]',
                    $this->getSalesOrder()->getIncrementId(),
                    $this->getClientOrder()->getAvocadoOrderId()
                ),
                Status::WARNING
            );
            return $this;
        }

        $invoice = $this->invoiceService->prepareInvoice($this->getSalesOrder());
        if (!$invoice || !$invoice->getTotalQty()) {
            throw new LocalizedException(__('Could not create invoice.'));
        }

        $invoice->setRequestedCaptureCase(OrderInvoice::CAPTURE_OFFLINE);
        $invoice->setTransactionId(time())
            ->addComment(
                __('Avocado Synchronisation. [Avocado Order: %1]', $this->getClientOrder()->getAvocadoOrderId()),
                false
            )->setCustomerNoteNotify(false);

        $invoice->register();
        $invoice->getOrder()->setIsInProcess(true);

        $this->transactionFactory
            ->create()
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();

        $this->addResponse(
            __(
                'Invoice has been created. [Order: %1, Invoice: %2, Avocado Order: %3]',
                $this->getSalesOrder()->getIncrementId(),
                $invoice->getIncrementId(),
                $this->getClientOrder()->getAvocadoOrderId()
            ),
            Status::SUCCESS
        );

        return $this;
    }

    /**
     * @return OrderInterface|null
     * @throws LocalizedException
     */
    private function getClientOrder()
    {
        if (null === $this->clientOrderEntity) {
            throw new LocalizedException(__('Client order entity is not set.'));
        }
        return $this->clientOrderEntity;
    }

    /**
     * @param OrderInterface $order
     * @return $this
     */
    private function setClientOrder(OrderInterface $order)
    {
        $this->clientOrderEntity = $order;
        return $this;
    }

    /**
     * @return SalesOrderInterface|null
     * @throws LocalizedException
     */
    private function getSalesOrder()
    {
        if (null === $this->orderEntity) {
            throw new LocalizedException(__('Order entity is not set.'));
        }
        return $this->orderEntity;
    }

    /**
     * @param SalesOrderInterface $salesOrder
     * @return $this
     */
    private function setSalesOrder(SalesOrderInterface $salesOrder)
    {
        $this->orderEntity = $salesOrder;
        return $this;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    private function generateBillingAddress()
    {
        $clientShippingEntry = $this->getClientOrder()->getShippingEntry();
        if (!$clientBillingEntry = $this->getClientOrder()->getBillingEntry()) {
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

        if (!isset(
            $clientBillingEntry[ClientOrderMetadataInterface::BILL_CITY],
            $clientBillingEntry[ClientOrderMetadataInterface::BILL_COUNTRY],
            $clientBillingEntry[ClientOrderMetadataInterface::BILL_POSTCODE]
        )
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
    private function generateShippingAddress()
    {
        $clientBillingEntry = $this->getClientOrder()->getBillingEntry();
        if (!$clientShippingEntry = $this->getClientOrder()->getShippingEntry()) {
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

        if (!isset(
            $clientShippingEntry[ClientOrderMetadataInterface::SHIP_CITY],
            $clientShippingEntry[ClientOrderMetadataInterface::SHIP_COUNTRY],
            $clientShippingEntry[ClientOrderMetadataInterface::SHIP_POSTCODE]
        )
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
    private function getIsAdmin()
    {
        return $this->appState->getAreaCode() == Area::AREA_ADMINHTML;
    }
}
