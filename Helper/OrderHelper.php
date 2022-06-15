<?php
/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Crypto\MetamaskEthPayment\Helper;

use Magento\Sales\Model\OrderRepository;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;

class OrderHelper
{
    private OrderRepository $orderRepository;
    private SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory;
    private OrderFactory $orderFactory;

    public function __construct(
        OrderRepository $orderRepository,
        SearchCriteriaBuilderFactory $criteriaBuilderFactory,
        OrderFactory $orderFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilderFactory = $criteriaBuilderFactory;
        $this->orderFactory = $orderFactory;
    }

    public function getOrderByHash(string $orderHash): ?OrderInterface
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteriaBuilder->addFilter('order_hash', $orderHash, 'eq');
        $searchCriteria = $searchCriteriaBuilder->create();
        $result = $this->orderRepository->getList($searchCriteria);

        return current($result->getItems());
    }

    public function getOrderByIncrementId(string $incrementId): ?OrderInterface
    {
        /** @var Order $order */
        $order = $this->orderFactory->create();
        $order->loadByIncrementId($incrementId);

        return $order;
    }
}
