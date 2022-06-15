<?php
/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Crypto\MetamaskEthPayment\Controller\Payment;

use Magento\Framework\Controller\Result\Json as ResultJson;
use Crypto\MetamaskEthPayment\Controller\Transactions;

class TxAdd extends Transactions
{
    /**
     * @return bool|\Magento\Framework\App\ResponseInterface|ResultJson|\Magento\Framework\Controller\ResultInterface
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

        $transaction = $this->transactionHandler->createTransaction($this->order, ['txhash' => $this->transactionHash]);
        if ($transaction) {
            $param['error'] = 0;
            $param['status'] = true;
            $param['message'] = __('Transaction is created and in processing.');
        } else {
            $param['error'] = 1;
            $param['message'] = __('Something went wrong. Transaction is not authorized.');
        }

        return $this->result->setData($param);
    }
}
