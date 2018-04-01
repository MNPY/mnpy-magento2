<?php

namespace Mnpy\Magento2\Model\Payment;

use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order;
use Magento\Payment\Model\Method\AbstractMethod;
use MNPY\SDK\Token;
use MNPY\SDK\Transaction;
use Magento\Framework\Model\Context;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\Logger;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Checkout\Model\Session as CheckoutSession;
use Mnpy\Magento2\Helper\Data as MnpyHelper;

/**
 * Class MNPY
 * @package Mnpy\Magento2\Model\Payment
 */
class MNPY extends AbstractMethod
{
    /** @var string */
    protected $_code = "mnpy";

    /** @var bool */
    protected $_isGateway = true;

    /** @var bool */
    protected $_canRefund = false;

    /** @var Transaction */
    protected $transaction;

    /** @var mnpyHelper */
    protected $mnpyHelper;

    /**
     * MNPY constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        MnpyHelper $mnpyHelper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->mnpyHelper = $mnpyHelper;
    }

    /**
     * @return int
     */
    private function getStoreId(): int
    {
        return $this->mnpyHelper->getStoreId();
    }

    /**
     * @param CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(
        CartInterface $quote = null
    ) {

        /**
         * Check if API key is set
         */
        if (!$this->mnpyHelper->hasApiKey($this->getStoreId())) {
            return false;
        }

        /**
         * Check if active
         */
        if ($this->mnpyHelper->getActive($this->getStoreId()) == false) {
            return false;
        }

        /**
         * Check if quote currency is supported
         */
        if (!$this->mnpyHelper->isAllowedCurrency(
            $quote->getQuoteCurrencyCode(),
            $this->getStoreId())
        ) {
            return false;
        }

        /**
         * Check if MNPY user has enough balance to pay for transaction
         */
        if (!$this->mnpyHelper->hasSufficientalance() &&
            $this->mnpyHelper->getModus($this->getStoreId()) === 'live'
        ) {
            return false;
        }

        /**
         * All good! Allow this payment method to be used
         */
        return parent::isAvailable($quote);
    }

    /**
     * @param Order $order
     * @return string
     */
    public function startTransaction(Order $order): string
    {
        try {
            $transaction = new Transaction($this->mnpyHelper->getFullApiKey($this->getStoreId()));
            $tx = $transaction->create(
                $this->mnpyHelper->getStoreName($this->getStoreId()),
                $order->getSubtotalInclTax(),
                $this->mnpyHelper->getEthAddress($this->getStoreId()),
                $this->mnpyHelper->getRedirectUrl($order->getId()),
                [
                    'fee' => $this->mnpyHelper->getFee($this->getStoreId()),
                    'fiat_currency' => $order->getBaseCurrencyCode(),
                    'code' => $order->getPayment()->getAdditionalInformation('selected_token'),
                    'callback_url' => $this->mnpyHelper->getCallbackUrl(),
                    'meta_data' => json_encode(
                        [
                            "order_id" => $order->getIncrementId()
                        ]
                    )
                ]
            );
            if ($order->getMnpyTransactionId() == null) {
                $order->setMnpyTransactionId($tx->uuid);
                $order->addStatusHistoryComment(
                    'MNPY Transaction started: <br />' .
                    ' Transaction id: ' . $tx->uuid . '<br />' .
                    ' Transaction url:' . $tx->payment_url . '<br />' .
                    ' Amount:' . $tx->funds->expected . $tx->funds->token->symbol
                );
                $order->save();
            }
            return $tx->payment_url;
        } catch (\Exception $e) {
            /**
             * @todo implement logger
             */
        }

    }

    /**
     * @param DataObject $data
     * @return $this
     */
    public function assignData(DataObject $data)
    {
        parent::assignData($data);

        $additionalData = $data->getAdditionalData();
        if (isset($additionalData['selected_token'])) {
            $token = $additionalData['selected_token'];
            $this->getInfoInstance()->setAdditionalInformation('selected_token', $token);
        }

        return $this;
    }
}
