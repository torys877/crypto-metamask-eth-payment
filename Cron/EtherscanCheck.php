<?php
/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Crypto\MetamaskEthPayment\Cron;

use Crypto\MetamaskEthPayment\Helper\TransactionHelper;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection as TransactionCollection;
use Magento\Sales\Model\Order\Payment\Transaction;
use Crypto\MetamaskEthPayment\Helper\OrderHelper;

/**
 * Cron checks etherscan transaction status and processes transaction,invoice, order
 */
class EtherscanCheck
{
    private TransactionHelper $transactionHelper;
    private OrderHelper $orderHelper;

    public function __construct(
        TransactionHelper $transactionHelper,
        OrderHelper $orderHelper
    ) {
        $this->transactionHelper = $transactionHelper;
        $this->orderHelper = $orderHelper;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        /** @var TransactionCollection $transactionCollection */
        $transactionCollection = $this->transactionHelper->getUnprocessedTransactions();
        if (!$transactionCollection->count()) {
            return;
        }

        foreach ($transactionCollection->getItems() as $transactionItem) {

            /** @var Transaction $transactionItem */
            $transactionItem->getIncrementId();
            $order = $this->orderHelper->getOrderByIncrementId($transactionItem->getIncrementId());
            if (!$order->getId()) {
                continue;
            }

            $this->transactionHelper->checkAndCapture($order, $transactionItem->getTxnId());
        }
    }
}
