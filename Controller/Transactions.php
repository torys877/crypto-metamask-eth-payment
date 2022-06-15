<?php
/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Crypto\MetamaskEthPayment\Controller;

use Crypto\MetamaskEthPayment\Helper\OrderHelper;
use Crypto\MetamaskEthPayment\Helper\TransactionHelper;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Controller\ResultInterface;

abstract class Transactions implements ActionInterface
{
    protected TransactionHelper $transactionHandler;
    protected ResultFactory $resultFactory;
    protected OrderHelper $orderHelper;
    /**
     * @var ResultJson|ResultInterface
     */
    protected $result;
    protected OrderInterface $order;
    protected RequestInterface $request;
    protected ?string $transactionHash;
    protected ?string $orderHash;

    public function __construct(
        TransactionHelper $transactionHandler,
        ResultFactory     $resultFactory,
        OrderHelper       $orderHelper,
        RequestInterface  $request
    ) {
        $this->transactionHandler = $transactionHandler;
        $this->resultFactory = $resultFactory;
        $this->orderHelper = $orderHelper;
        $this->request = $request;
    }

    /**
     * @return bool|ResultJson
     */
    public function checkParams()
    {
        /** @var ResultJson $result */
        $this->result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $param = [
            'status' => false
        ];
        $this->transactionHash = $this->getRequest()->getParam('txhash', null);
        $this->orderHash = $this->getRequest()->getParam('order_hash', null);

        if (!$this->transactionHash || !$this->orderHash) {
            $param['error'] = 1;
            $param['message'] = __('Transaction hash or order hash is not send.');
            return $this->result->setData($param);
        }

        $this->order = $this->orderHelper->getOrderByHash($this->orderHash);

        if (!$this->order->getId()) {
            $param['error'] = 1;
            $param['message'] = __('Order is not found by hash.');

            return $this->result->setData($param);
        }

        return true;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
