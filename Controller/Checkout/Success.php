<?php

namespace Mnpy\Magento2\Controller\Checkout;

use Mnpy\Magento2\Helper\Data as MnpyHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use MNPY\SDK\Transaction;

/**
 * Class Success
 * @package Mnpy\Magento2\Controller\Checkout
 */
class Success extends Action
{

    /** @var Session */
    protected $checkoutSession;

    /** @var MnpyHelper */
    protected $mnpyHelper;

    /** @var OrderFactory */
    protected $orderFactory;

    /**
     * Success constructor.
     * @param Context $context
     * @param MnpyHelper $mnpyHelper
     * @param Session $checkoutSession
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Context $context,
        MnpyHelper $mnpyHelper,
        Session $checkoutSession,
        OrderFactory $orderFactory
    ) {
        parent::__construct($context);
        $this->mnpyHelper = $mnpyHelper;
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
    }

    /**
     * Redirect to success page
     * + set order on processing if mode is staging
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();

        $modus = $this->mnpyHelper->getModus($this->mnpyHelper->getStoreId());
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($modus === 'staging' && $this->mnpyHelper->hasApiKey($this->mnpyHelper->getStoreId())) {

            $checkTransaction = new Transaction($this->mnpyHelper->getFullApiKey($this->mnpyHelper->getStoreId()));

            if (isset($params['orderid'])) {
                try {
                    $order = $this->orderFactory->create()->load($params['orderid']);
                    $transaction = $checkTransaction->get($order->getMnpyTransactionId());
                    $status = $transaction->status;

                    if ($status === 'completed') {
                        $order->setStatus(Order::STATE_PROCESSING);
                        $order->save();

                        $this->checkoutSession->start();
                        $resultRedirect->setPath('checkout/onepage/success');
                    } else {
                        $this->paymentFailed($resultRedirect);
                    }
                } catch (\Exception $e) {
                    $this->paymentFailed($resultRedirect);
                }
            } else {
                $this->paymentFailed($resultRedirect);
            }
        } else {
            $this->checkoutSession->start();
            $resultRedirect->setPath('checkout/onepage/success');
        }

        return $resultRedirect;
    }

    /**
     * @param $resultRedirect
     */
    private function paymentFailed($resultRedirect)
    {
        $this->checkoutSession->restoreQuote();
        $this->messageManager->addError(__('Payment not completed'));
        $resultRedirect->setPath('checkout/cart');
    }
}
