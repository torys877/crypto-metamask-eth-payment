<?php
/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Crypto\MetamaskEthPayment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Crypto\MetamaskEthPayment\Gateway\Config\Config;
use Crypto\MetamaskEthPayment\Helper\ConfigReader;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'ether_payment';
    private Config $config;
    private Quote $quote;
    private StoreManagerInterface $storeManager;

    public function __construct(
        Config $config,
        Quote $quote,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->quote = $quote;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {
        if (
            !$this->config->isActive() ||
            $this->storeManager->getStore()->getCurrentCurrency()->getCode() != ConfigReader::PAYMENT_CURRENCY_CODE
        ) {
            return [];
        }

        $config = [
            'payment' => [
                ConfigReader::PAYMENT_CODE => [
                    'isActive' => $this->config->isActive(),
                    'title' => $this->config->getTitle(),
                    'merchant_ether_address' => $this->config->getMerchantEtherAddress()
                ]
            ]
        ];

        return $config;
    }
}
