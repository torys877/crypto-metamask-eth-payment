<?php
/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Crypto\MetamaskEthPayment\Helper;

use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Webapi\Rest\Request\Deserializer\Json;

class EtherscanHelper
{
    private const OK_MESSAGE = 'OK';
    private const NOTOK_MESSAGE = 'NOTOK';
    private LoggerInterface $logger;
    private ConfigReader $configReader;
    private Curl $curl;
    private Json $json;

    public function __construct(
        LoggerInterface $logger,
        ConfigReader $configReader,
        Curl $curl,
        Json $json
    ) {
        $this->logger = $logger;
        $this->configReader = $configReader;
        $this->curl = $curl;
        $this->json = $json;
    }

    public function checkEtherscan(string $transactionHash)
    {
        $etherScanStatusUrl = $this->configReader->getEtherscanApiUrl() .
            '?module=transaction&action=gettxreceiptstatus&txhash=' . $transactionHash .
            '&apikey=' . $this->configReader->getEtherscanApiKey();
        try {
            $this->curl->get($etherScanStatusUrl);
            $response = $this->json->deserialize($this->curl->getBody());
        } catch (\Exception $ex) {
            $this->logger->critical(
                'Transaction with hash "' . $transactionHash . ' has CURL error, check logs'
            );
            $this->logger->critical($ex->getMessage());
            $this->logger->critical($ex->getTraceAsString());

            return false;
        }

        if (
            isset($response['status']) && isset($response['message']) &&
            $response['status'] &&
            $response['message'] == self::OK_MESSAGE &&
            !empty($response['result']['status']) &&
            $response['result']['status'] &&
            $this->checkEtherscanData($transactionHash)
        ) {
            return true;
        }

        return false;
    }

    protected function checkEtherscanData(string $transactionHash)
    {
        $etherScanCheckUrl = $this->configReader->getEtherscanApiUrl() .
            '?module=proxy&action=eth_getTransactionByHash&txhash=' . $transactionHash .
            '&apikey=' . $this->configReader->getEtherscanApiKey();
        try {
            $this->curl->get($etherScanCheckUrl);
            $response = $this->json->deserialize($this->curl->getBody());
        } catch (\Exception $ex) {
            $this->logger->critical(
                'Transaction detail api with hash "' . $transactionHash . ' has CURL error, check logs'
            );
            $this->logger->critical($ex->getMessage());
            $this->logger->critical($ex->getTraceAsString());

            return false;
        }

        if (isset($response['error'])) {
            return false;
        }

        if (
            isset($response['result']) &&
            isset($response['result']['to']) &&
            $response['result']['to'] == $this->configReader->getMerchantAddress()
        ) {
            return true;
        }

        return false;

    }
}
