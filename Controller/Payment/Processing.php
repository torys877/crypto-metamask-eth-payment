<?php
/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Crypto\MetamaskEthPayment\Controller\Payment;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Result\Page;
use Magento\Checkout\Controller\Action;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Result\PageFactory;
use Crypto\MetamaskEthPayment\ViewModel\EtherProcessing;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class Processing extends Action implements ActionInterface
{
    protected PageFactory $resultPageFactory;
    protected CheckoutSession $checkoutSession;
    protected Registry $registry;
    protected OrderRepository $orderRepository;
    protected SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        CheckoutSession $checkoutSession,
        PageFactory $resultPageFactory,
        Registry $registry,
        OrderRepository $orderRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->checkoutSession = $checkoutSession;
        $this->registry = $registry;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;

        parent::__construct($context, $customerSession, $customerRepository,$accountManagement);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $orderHash = $this->getRequest()->getParam('order_hash');
        $order = $this->getOrder($orderHash);
        $checkoutRealOrder = $this->checkoutSession->getLastRealOrder();
        $incrementId = '';

        if ($orderHash && $order) {
            $incrementId = $order->getIncrementId();
            $this->registry->register('current_order', $order);
            /** @var Template $block */
            $block = $resultPage->getLayout()->getBlock('ether_processing');
            /** @var EtherProcessing $viewModel */
            $viewModel = $block->getViewModel();
            $viewModel->setOrder($order);
            $viewModel->setRedirectToAccount((bool) $this->getRequest()->getParam('redirect_to_account'));
        } elseif ($checkoutRealOrder) {
            $incrementId = $checkoutRealOrder->getIncrementId();
            if (!$this->_preDispatchValidateCustomer() || !$this->isValid()) {
                return $this->resultRedirectFactory->create()->setPath('checkout/cart/index');
            }

            $this->registry->register('current_order', $checkoutRealOrder);
        }

        $resultPage->getConfig()->getTitle()->set((string) __('Processing Order #' . $incrementId));

        return $resultPage;
    }

    /**
     * @param string|null $orderHash
     * @return OrderInterface|Order|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function getOrder(?string $orderHash)
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder->addFilter('order_hash', $orderHash)->create();
        $result = $this->orderRepository->getList($searchCriteria);
        if (!$result->getTotalCount()) {
            return null;
        }
        $items = $result->getItems();
        $item = current($items);
        if ($item && $item->getEntityId() !== null) {
            return $this->orderRepository->get($item->getEntityId());
        }

        return null;
    }

    public function isValid(): bool
    {
        if (!$this->checkoutSession->getLastSuccessQuoteId()) {
            return false;
        }

        if (!$this->checkoutSession->getLastQuoteId() || !$this->checkoutSession->getLastOrderId()) {
            return false;
        }
        return true;
    }

}
