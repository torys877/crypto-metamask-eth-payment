<?php
/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Crypto\MetamaskEthPayment\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Crypto\MetamaskEthPayment\Helper\ConfigReader;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Framework\UrlInterface;
use Magento\Framework\Registry;

class EtherProcessing implements ArgumentInterface
{
    public const REQUEST_INTERVAL_SECONDS = 5000;
    private CurrentCustomer $currentCustomer;
    private TimezoneInterface $localeDate;
    private ScopeConfigInterface $configManager;
    private ConfigReader $configReader;
    private Session $checkoutSession;
    private ?Order $order = null;
    private UrlInterface $urlBuilder;
    private Registry $registry;
    private bool $redirectToAccount = false;

    public function __construct(
        CurrentCustomer $currentCustomer,
        TimezoneInterface $localeDate,
        ScopeConfigInterface $configManager,
        ConfigReader $configReader,
        Session $checkoutSession,
        UrlInterface $urlBuilder,
        Registry $registry
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->localeDate = $localeDate;
        $this->configManager = $configManager;
        $this->configReader = $configReader;
        $this->checkoutSession = $checkoutSession;
        $this->urlBuilder = $urlBuilder;
        $this->registry = $registry;
    }

    public function getOrder()
    {
        if (!$this->order) {
            $this->order = $this->checkoutSession->getLastRealOrder();
            if (!$this->order) {
                $this->registry->registry('current_order');
            }
        }

        return $this->order;
    }

    public function setOrder(?Order $order = null): self
    {
        $this->order = $order;

        return $this;
    }

    public function setRedirectToAccount(bool $redirect): self
    {
        $this->redirectToAccount = $redirect;

        return $this;
    }

    public function getRedirectToAccount(): bool
    {
        return $this->redirectToAccount;
    }

    public function getNetworkVersion(): ?string
    {
        return (string) $this->configReader->getNetworkVersion();
    }

    public function getMerchantAddress(): ?string
    {
        return (string) $this->configReader->getMerchantAddress();
    }

    public function getOrderIncrement(): ?string
    {
        return (string) $this->getOrder()->getIncrementId();
    }

    public function getOrderHash(): ?string
    {
        return (string) $this->getOrder()->getOrderHash();
    }

    public function getOrderEtherAmount(): float
    {
        return (float) $this->getOrder()->getGrandTotal();
    }

    public function getThCheckAndConfirmUrl(): string
    {
        return $this->urlBuilder->getUrl('ethpayment/payment/txCheckAndConfirm');
    }

    public function getAddTxUrl(): string
    {
        return $this->urlBuilder->getUrl('ethpayment/payment/txAdd');
    }

    public function getSuccessUrl(): string
    {
        if ($this->getRedirectToAccount()) {
            return $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $this->getOrder()->getId()]);
        }

        return $this->urlBuilder->getUrl('checkout/onepage/success');
    }

    public function getRequestIntervalSeconds(): int
    {
        return self::REQUEST_INTERVAL_SECONDS;
    }
}
