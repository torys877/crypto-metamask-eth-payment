<?php
/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Crypto\MetamaskEthPayment\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Repository;
use Crypto\MetamaskEthPayment\Helper\ConfigReader;

class TxnHtml implements ObserverInterface
{
    protected Repository $paymentRepository;
    protected ConfigReader $configReader;

    public function __construct(
        Repository $paymentRepository,
        ConfigReader $configReader
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->configReader = $configReader;
    }

    public function execute(EventObserver $observer): void
    {
        /** @var ?Transaction $txn */
        $txn = $observer->getData('order_payment_transaction');
        if ($txn) {
            $paymentId = $txn->getPaymentId();
            try {
                $payment = $this->paymentRepository->get($paymentId);
            } catch (NoSuchEntityException $ex) {
                //just skip
                return;
            }

            if ($payment->getMethod() == ConfigReader::PAYMENT_CODE) {
                $url = '<a href=\'' . $this->configReader->getEtherscanUrl() . '/' . $txn->getTxnId() . '\'>' .
                    $txn->getTxnId() . '</a>';
                $txn->setData('html_txn_id', $url);
            }
        }
    }
}
