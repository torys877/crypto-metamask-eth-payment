<?php
/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Crypto\MetamaskEthPayment\Gateway\Config;

use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Crypto\MetamaskEthPayment\Gateway\Config\Config as ConfigHelper;
use Magento\Store\Model\StoreManagerInterface;
use Crypto\MetamaskEthPayment\Helper\ConfigReader;

class ActiveValueHandler implements ValueHandlerInterface
{
    private StoreManagerInterface $storeManager;
    private ConfigHelper $config;

    public function __construct(
        StoreManagerInterface $storeManager,
        ConfigHelper $config
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    public function handle(array $subject, $storeId = null): bool
    {
        if (
            !$this->config->isActive() ||
            $this->storeManager->getStore()->getCurrentCurrency()->getCode() != ConfigReader::PAYMENT_CURRENCY_CODE
        ) {
            return false;
        }

        return true;
    }
}
