<?php
/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Crypto\MetamaskEthPayment\Controller\Payment;

use Magento\Framework\Controller\Result\Json as ResultJson;
use Crypto\MetamaskEthPayment\Controller\Transactions;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class TxCheckAndConfirm extends Transactions
{
    /**
     * @return bool|ResponseInterface|ResultJson|ResultInterface
     */
    public function execute()
    {
        $result = $this->checkParams();

        if ($result instanceof ResultJson) {
            return $result;
        }

        if (!$this->order->getEntityId()) {
            $param['error'] = 1;
            $param['message'] = __('Order is not found by hash.');

            return $this->result->setData($param);
        }

        $result = $this->transactionHandler->checkAndCapture($this->order, (string) $this->transactionHash);

        if ($result) {
            $param['error'] = 0;
            $param['status'] = true;
            $param['message'] = __('Transaction is captured.');
        } else {
            $param['error'] = 1;
            $param['message'] = __('Transaction is checking. Please wait to check transaction or connect with merchant.');
        }

        return $this->result->setData($param);
    }
}
