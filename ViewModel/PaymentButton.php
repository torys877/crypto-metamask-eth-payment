<?php
/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Crypto\MetamaskEthPayment\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Crypto\MetamaskEthPayment\Model\Ui\ConfigProvider;
use Crypto\MetamaskEthPayment\Helper\ConfigReader;

class PaymentButton implements ArgumentInterface
{
    public const PAYMENT_PENDING_STATUS = 'pending';
    private OrderRepository $orderRepository;
    private RequestInterface $request;
    private UrlInterface $url;
    /**
     * @var Order|null
     */
    private $order = null;
    private ?string $buttonUrl = null;

    public function __construct(
        OrderRepository $orderRepository,
        RequestInterface $request,
        UrlInterface $url
    ) {
        $this->orderRepository = $orderRepository;
        $this->request = $request;
        $this->url = $url;
    }

    /**
     * @return OrderInterface|Order|null
     */
    public function getOrder()
    {
        if (!$this->order) {
            $orderId = $this->request->getParam('order_id');
            if ($orderId) {
                /** @var Order $order */
                $order = $this->orderRepository->get($orderId);;
                $this->order = $order;
            }
        }

        return $this->order;
    }

    public function isShowButton(): bool
    {
        if (!$this->getOrder()) {
            return false;
        }

        /** @var Payment $payment */
        $payment = $this->getOrder()->getPayment();
        if (
            $payment->getMethod() == ConfigReader::PAYMENT_CODE &&
            $this->getOrder()->getState() == Order::STATE_NEW &&
            $this->getOrder()->getStatus() == self::PAYMENT_PENDING_STATUS &&
            $this->getButtonLink()
        ) {

            return true;
        }

        return false;
    }

    public function isTransactionPendingCapture(): bool
    {
        /** @var Payment $payment */
        $payment = $this->getOrder()->getPayment();
        if (
            $payment->getMethod() == ConfigReader::PAYMENT_CODE &&
            $payment->getLastTransId() &&
            $this->getOrder()->getState() == Order::STATE_NEW &&
            $this->getOrder()->getStatus() == self::PAYMENT_PENDING_STATUS
        ) {
            return true;
        }

        return false;
    }

    public function isTransactionCaptured(): bool
    {
        /** @var Payment $payment */
        $payment = $this->getOrder()->getPayment();
        if (
            $payment->getMethod() == ConfigReader::PAYMENT_CODE &&
            $payment->getLastTransId() &&
            $this->order->hasInvoices() &&
            $this->order->getStatus() == Order::STATE_PROCESSING &&
            $this->order->getGrandTotal() == $this->order->getTotalPaid()
        ) {
            return true;
        }

        return false;
    }

    public function getButtonLink(): ?string
    {
        if (!$this->buttonUrl) {
            $orderHash = $this->getOrder()->getData('order_hash');
            if ($orderHash) {
                $this->buttonUrl = $this->url->getUrl(
                    'ethpayment/payment/processing',
                    [
                        'order_hash' => $orderHash,
                        'redirect_to_account' => true
                    ]
                );
            }
        }

        return $this->buttonUrl;
    }
}
