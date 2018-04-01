<?php

namespace Mnpy\Magento2\Controller\Checkout;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Mnpy\Magento2\Helper\Data;
use MNPY\SDK\Transaction;

/**
 * Class Webhook
 * @package Mnpy\Magento2\Controller\Checkout
 */
class Webhook extends Action
{
    /**@var OrderFactory */
    protected $orderFactory;

    /** @var Data */
    protected $mnpyHelper;

    /**
     * Webhook constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        Data $mnpyHelper
    )
    {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
        $this->mnpyHelper = $mnpyHelper;
    }

    /**
     * @return bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $signature = $_SERVER['HTTP_X_MNPY_SIGNATURE'];

        /**
         * String to int conversion
         */
        if (isset($data['updated_at']['timezone_type'])) {
            $data['updated_at']['timezone_type'] = (int) $data['updated_at']['timezone_type'];
        }

        try {
            $order = $this->orderFactory->create()->loadByAttribute('mnpy_transaction_id', $data['uuid']);

            if (!$this->checkSignature($this->mnpyHelper->getCallbackUrl(), $data, $signature)) {
                return false;
            }

            $checkTransaction = new Transaction($this->mnpyHelper->getFullApiKey($this->mnpyHelper->getStoreId()));
            $transaction = $checkTransaction->get($order->getMnpyTransactionId());
            $status = $transaction->status;

            if ($status === 'completed') {
                $order->setStatus(Order::STATE_PROCESSING);
                $order->save();
            }
            if ($status === 'late') {
                $order->setStatus($this->mnpyHelper->getLateStatus($this->mnpyHelper->getStoreId()));
                $order->save();
            }
            
        } catch (\Exception $e) {
            /**
             * @todo: implement logging
             */

            return false;
        }
        
        return true;
    }

    /**
     * @param $url
     * @param array $data
     * @param $verify
     * @return bool
     */
    private function checkSignature($url, array $data, $verify): bool
    {
        try {
            $signature = $url;
            $signature .= json_encode($data);

            $checksum = base64_encode(hash_hmac(
                    'sha1',
                    $signature,
                    $this->mnpyHelper->getApiSecretKey($this->mnpyHelper->getStoreId()),
                    true)
            );
            if ($checksum !== $verify) {
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            /**
             * @todo: implement logging
             */
            return false;
        }

    }
}
