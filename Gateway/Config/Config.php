<?php
/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Crypto\MetamaskEthPayment\Gateway\Config;

use Crypto\MetamaskEthPayment\Helper\ConfigReader as ConfigHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Payment\Gateway\Config\Config as BaseConfig;

class Config extends BaseConfig
{
    const KEY_ACTIVE = 'active';
    const KEY_TITLE = 'title';
    const KEY_MERCHANT_ETHER_ADDRESS = 'merchant_ether_address';
    protected StoreManagerInterface $storeManager;
    protected ConfigHelper $config;

    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);

        $this->storeManager = $storeManager;
    }

    public function isActive(int $storeId = null): bool
    {
        return (bool) $this->getValue(
            self::KEY_ACTIVE,
            $storeId ?? $this->storeManager->getStore()->getId()
        );
    }

    public function getTitle(int $storeId = null): bool
    {
        return (bool) $this->getValue(
            self::KEY_TITLE,
            $storeId ?? $this->storeManager->getStore()->getId()
        );
    }

    public function getMerchantEtherAddress(int $storeId = null): bool
    {
        return (bool) $this->getValue(
            self::KEY_MERCHANT_ETHER_ADDRESS,
            $storeId ?? $this->storeManager->getStore()->getId()
        );
    }
}
