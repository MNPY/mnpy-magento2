<?php

namespace Mnpy\Magento2\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Payment\Helper\Data as PaymentHelper;
use Mnpy\Magento2\Model\Payment\MNPY;

/**
 * Class Redirect
 * @package Mnpy\Magento2\Controller\Checkout
 */
class Redirect extends Action
{
    /** @var Session */
    protected $checkoutSession;

    /**
     * Redirect constructor.
     *
     * @param Context $context
     * @param Session $checkoutSession
     * @param PaymentHelper $paymentHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        PaymentHelper $paymentHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->paymentHelper = $paymentHelper;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();

        /**
         * Check if order exists
         */
        if (!$order->getId()) {
            $this->messageManager->addError(__('Order not found, cannot proceed to MNPY'));
            return $this->getResponse()->setRedirect('/checkout/cart');
        }

        /**
         * Get payment method
         */
        $method = $order->getPayment()->getMethod();
        $methodInstance = $this->paymentHelper->getMethodInstance($method);

        if ($methodInstance instanceof MNPY === false) {
            $this->messageManager->addError(__('Payment method not found.'));
            $this->checkoutSession->restoreQuote();
            /** Redirect to cart */
            return $this->getResponse()->setRedirect('/checkout/cart');
        }

        $redirectUrl = $methodInstance->startTransaction($order);

        /** Redirect to MNPY */
        return $this->getResponse()->setRedirect($redirectUrl);
    }
}
