<?php
/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Crypto\MetamaskEthPayment\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigReader
{
    public const PAYMENT_CODE = 'ether_payment';
    public const PAYMENT_CURRENCY_CODE = 'ETH';

    private const CONFIG_ETHER_PAYMENT_ACTIVE = 'payment/ether_payment/active';
    private const CONFIG_ETHER_PAYMENT_TITLE = 'payment/ether_payment/title';
    private const CONFIG_ETHER_PAYMENT_NETWORK_VERSION = 'payment/ether_payment/network_version';
    private const CONFIG_ETHER_PAYMENT_MERCHANT_ADDRESS = 'payment/ether_payment/merchant_ether_address';

    private const CONFIG_ETHER_PAYMENT_ETHERSCAN_URL = 'payment/ether_payment/etherscan_url';
    private const CONFIG_ETHER_PAYMENT_ETHERSCAN_CHECK_ON = 'payment/ether_payment/etherscan_check';
    private const CONFIG_ETHER_PAYMENT_ETHERSCAN_API_URL = 'payment/ether_payment/etherscan_api_url';
    private const CONFIG_ETHER_PAYMENT_ETHERSCAN_API_KEY = 'payment/ether_payment/etherscan_api_key';

    private const CONFIG_TEST_ETHER_PAYMENT_ACTIVE = 'payment/ether_payment/test_mode/active';
    private const CONFIG_TEST_ETHER_PAYMENT_NETWORK_VERSION = 'payment/ether_payment/test_mode/network_version';
    private const CONFIG_TEST_ETHER_PAYMENT_MERCHANT_ADDRESS = 'payment/ether_payment/test_mode/merchant_ether_address';

    private const CONFIG_TEST_ETHER_PAYMENT_ETHERSCAN_URL = 'payment/ether_payment/test_mode/etherscan_url';
    private const CONFIG_TEST_ETHER_PAYMENT_ETHERSCAN_CHECK_ON = 'payment/ether_payment/test_mode/etherscan_check';
    private const CONFIG_TEST_ETHER_PAYMENT_ETHERSCAN_API_URL = 'payment/ether_payment/test_mode/etherscan_api_url';
    private const CONFIG_TEST_ETHER_PAYMENT_ETHERSCAN_API_KEY = 'payment/ether_payment/test_mode/etherscan_api_key';

    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_ETHER_PAYMENT_ACTIVE);
    }

    /**
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_ETHER_PAYMENT_TITLE);
    }

    /**
     * @return string
     */
    public function getNetworkVersion(): ?string
    {
        if ($this->isTestEnabled()) {
            return $this->getTestNetworkVersion();
        }

        return $this->scopeConfig->getValue(self::CONFIG_ETHER_PAYMENT_NETWORK_VERSION);
    }

    /**
     * @return string
     */
    public function getMerchantAddress(): ?string
    {
        if ($this->isTestEnabled()) {
            return $this->getTestMerchantAddress();
        }

        return $this->scopeConfig->getValue(self::CONFIG_ETHER_PAYMENT_MERCHANT_ADDRESS);
    }

    /**
     * @return string
     */
    public function getEtherscanUrl(): ?string
    {
        if ($this->isTestEnabled()) {
            return $this->getTestEtherscanUrl();
        }

        return $this->scopeConfig->getValue(self::CONFIG_ETHER_PAYMENT_ETHERSCAN_URL);
    }

    /**
     * @return string
     */
    public function getEtherscanCheckOn(): ?string
    {
        if ($this->isTestEnabled()) {
            return $this->getTestEtherscanCheckOn();
        }

        return $this->scopeConfig->getValue(self::CONFIG_ETHER_PAYMENT_ETHERSCAN_CHECK_ON);
    }

    /**
     * @return string
     */
    public function getEtherscanApiUrl(): ?string
    {
        if ($this->isTestEnabled()) {
            return $this->getTestEtherscanApiUrl();
        }

        return $this->scopeConfig->getValue(self::CONFIG_ETHER_PAYMENT_ETHERSCAN_API_URL);
    }

    /**
     * @return string
     */
    public function getEtherscanApiKey(): ?string
    {
        if ($this->isTestEnabled()) {
            return $this->getTestEtherscanApiKey();
        }

        return $this->scopeConfig->getValue(self::CONFIG_ETHER_PAYMENT_ETHERSCAN_API_KEY);
    }

    /**
     * @return bool
     */
    public function isTestEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_TEST_ETHER_PAYMENT_ACTIVE);
    }

    /**
     * @return string
     */
    public function getTestNetworkVersion(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_TEST_ETHER_PAYMENT_NETWORK_VERSION);
    }

    /**
     * @return string
     */
    public function getTestMerchantAddress(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_TEST_ETHER_PAYMENT_MERCHANT_ADDRESS);
    }

    /**
     * @return string
     */
    public function getTestEtherscanUrl(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_TEST_ETHER_PAYMENT_ETHERSCAN_URL);
    }

    /**
     * @return string
     */
    public function getTestEtherscanCheckOn(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_TEST_ETHER_PAYMENT_ETHERSCAN_CHECK_ON);
    }

    /**
     * @return string
     */
    public function getTestEtherscanApiUrl(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_TEST_ETHER_PAYMENT_ETHERSCAN_API_URL);
    }

    /**
     * @return string
     */
    public function getTestEtherscanApiKey(): ?string
    {
        return $this->scopeConfig->getValue(self::CONFIG_TEST_ETHER_PAYMENT_ETHERSCAN_API_KEY);
    }

    public function isEtherscanCheck(): bool
    {
        if (
            $this->getEtherscanCheckOn() &&
            $this->getEtherscanApiUrl() &&
            $this->getEtherscanApiKey()
        ) {
            return true;
        }

        return false;
    }
}
