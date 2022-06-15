<?php
/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Crypto\MetamaskEthPayment\Helper;

use Exception;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction as DbTransaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\InvoiceRepository;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection as TransactionCollection;

class TransactionHelper
{
    public const UNPROCESSED_TRANSACTION_TIME = 24;

    private BuilderInterface $transactionBuilder;
    private TransactionRepositoryInterface $transactionRepository;
    private DbTransaction $dbTransaction;
    private InvoiceService $invoiceService;
    private InvoiceSender $invoiceSender;
    private LoggerInterface $logger;
    private InvoiceRepository $invoiceRepository;
    private OrderRepository $orderRepository;
    private ConfigReader $configReader;
    private EtherscanHelper $etherscanHelper;
    private SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory;

    public function __construct(
        BuilderInterface $transactionBuilder,
        TransactionRepositoryInterface $transactionRepository,
        DbTransaction $dbTransaction,
        InvoiceService $invoiceService,
        InvoiceSender $invoiceSender,
        LoggerInterface $logger,
        InvoiceRepository $invoiceRepository,
        OrderRepository $orderRepository,
        ConfigReader $configReader,
        EtherscanHelper $etherscanHelper,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {
        $this->transactionBuilder = $transactionBuilder;
        $this->transactionRepository = $transactionRepository;
        $this->dbTransaction = $dbTransaction;
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;
        $this->logger = $logger;
        $this->invoiceRepository = $invoiceRepository;
        $this->orderRepository = $orderRepository;
        $this->configReader = $configReader;
        $this->etherscanHelper = $etherscanHelper;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    public function createTransaction(OrderInterface $order, array $paymentData): ?string
    {
        try {
            $payment = $order->getPayment();
            $payment->setLastTransId($paymentData['txhash']);
            $payment->setTransactionId($paymentData['txhash']);
            $payment->setAdditionalInformation(
                [Transaction::RAW_DETAILS => (array) $paymentData]
            );
            $formatedPrice = $order->getGrandTotal();

            $message = __(
                'Transaction is in processing. The authorized amount is %1 ETH. Waiting for capture.',
                $formatedPrice
            );

            $transaction = $this->transactionBuilder
                ->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($paymentData['txhash'])
                ->setAdditionalInformation(
                    [Transaction::RAW_DETAILS => (array) $paymentData]
                )
                ->setFailSafe(true)
                ->build(Transaction::TYPE_AUTH);
            $transaction->setIsClosed(0);

            $payment->addTransactionCommentsToOrder(
                $transaction,
                $message
            );
            $payment->setParentTransactionId(null);
            $payment->save();
            $order->save();

            $transactionEntity = $this->transactionRepository->save($transaction);
            $transId = null;
            if ($transactionEntity->getTransactionId()) {
                $transId = $transactionEntity->getTransactionId();
            }
            return $transId;
        } catch (Exception $ex) {
            $this->logger->critical($ex->getMessage());
            $this->logger->critical($ex->getTraceAsString());

            return null;
        }
    }

    public function checkAndCapture(OrderInterface $order, string $transactionHash): bool
    {
        $payment = $order->getPayment();
        if (!$payment) {
            $this->logger->critical(
                'Payment for transaction "' . $transactionHash .
                '", orderId = ' . $order->getIncrementId() .
                ' does not exist.'
            );

            return false;
        }

        /** @var TransactionInterface $transaction */
        $transaction = $this->transactionRepository->getByTransactionId(
            $transactionHash,
            $order->getEntityId(),
            $payment->getEntityId()
        );

        if (!$transaction) {
            $this->logger->critical(
                'Transaction with hash "' . $transactionHash .
                '", orderId = ' . $order->getIncrementId() .
                ', paymentId = ' . $payment->getEntityId() . ' does not exist.'
            );

            return false;
        }

        if ($this->configReader->isEtherscanCheck()) {
            if (!$this->etherscanHelper->checkEtherscan($transactionHash)) {
                return false;
            }
        }

        $transaction->setTxnType(TransactionInterface::TYPE_CAPTURE);
        $transaction->setIsClosed(1);
        $this->transactionRepository->save($transaction);

        try {
            $this->createInvoice($order, $transactionHash);
        } catch (Exception $ex) {
            $this->logger->critical($ex->getMessage());
            $this->logger->critical($ex->getTraceAsString());

            return false;
        }

        return true;
    }

    public function createInvoice(OrderInterface $order, string $transactionHash): bool
    {
        /** @var Order $order */
        if (!$order->canInvoice()) {
            return false;
        }

        /** @var Invoice $invoice */
        $invoice = $this->invoiceService->prepareInvoice($order);
        $invoice->setTransactionId($transactionHash);
        $invoice->register();
        $invoice->pay();

        $this->dbTransaction = $this->dbTransaction
            ->addObject($invoice)
            ->addObject($invoice->getOrder());

        $this->dbTransaction->save();

        $this->invoiceSender->send($invoice);

        $order->addCommentToStatusHistory(__('Notified customer about invoice creation #%1.', $invoice->getId()))
            ->setIsCustomerNotified(true);
        $order->setStatus(Order::STATE_PROCESSING);
        $this->orderRepository->save($order);

        return true;
    }

    public function getUnprocessedTransactions(): TransactionCollection
    {
        //check for last day
        $dateStart = new \DateTime();
        $dateStart->modify('-' . self::UNPROCESSED_TRANSACTION_TIME . ' hours');

        $criteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $criteriaBuilder->addFilter('is_closed', 0);
        $criteriaBuilder->addFilter('created_at', $dateStart, 'gt');

        return $this->transactionRepository->getList($criteriaBuilder->create());
    }
}
