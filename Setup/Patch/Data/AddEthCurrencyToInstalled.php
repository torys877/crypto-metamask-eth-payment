<?php
/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Crypto\MetamaskEthPayment\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class AddEthCurrencyToInstalled implements DataPatchInterface
{
    private string $installedCurrenciesPath = 'system/currency/installed';
    private ModuleDataSetupInterface $moduleDataSetup;
    private WriterInterface $configWriter;
    private ScopeConfigInterface $config;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $configWriter,
        ScopeConfigInterface $config
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->configWriter = $configWriter;
        $this->config = $config;
    }

    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();
        $installedCurrencies = explode(',', $this->config->getValue(
            $this->installedCurrenciesPath
        ));
        $installedCurrencies[] = 'ETH';

        $this->configWriter->save(
            $this->installedCurrenciesPath,
            implode(',', $installedCurrencies)
        );

        $this->moduleDataSetup->endSetup();
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
