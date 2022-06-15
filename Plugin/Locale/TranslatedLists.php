<?php
/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Crypto\MetamaskEthPayment\Plugin\Locale;

use Magento\Framework\Locale\TranslatedLists as BaseTranslatedLists;

class TranslatedLists
{
    public function afterGetOptionCurrencies(BaseTranslatedLists $subject, array $result): array
    {
        return $this->addEthCurrency($result);
    }

    public function afterGetOptionAllCurrencies(BaseTranslatedLists $subject, array $result): array
    {
        return $this->addEthCurrency($result);
    }

    protected function addEthCurrency(array $data): array
    {
        $currency = [
            ['value' => 'ETH', 'label' => 'Ether']
        ];

        $result = array_merge($data, $currency);

        return $this->_sortOptionArray($result);
    }

    /**
     * Sort option array.
     *
     * @param array $option
     * @return array
     */
    protected function _sortOptionArray(array $option): array
    {
        $data = [];
        foreach ($option as $item) {
            $data[$item['value']] = $item['label'];
        }
        asort($data);
        $option = [];
        foreach ($data as $key => $label) {
            $option[] = ['value' => $key, 'label' => $label];
        }
        return $option;
    }
}
