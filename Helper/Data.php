<?php
namespace Mnpy\Magento2\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 *
 * @package MNPY\Magento2\Helper
 */
class Data extends AbstractHelper
{
    /**
     * XML Paths
     */
    const XML_PATH_ACTIVE = 'payment/mnpy/active';
    const XML_PATH_APIKEY = 'payment/mnpy/apikey';
    const XML_PATH_APISECRET = 'payment/mnpy/apisecret';
    const XML_PATH_PROCESSING = 'payment/mnpy/processing';
    const XML_PATH_PENDING = 'payment/mnpy/pending';
    const XML_PATH_LATE = 'payment/mnpy/late';
    const XML_PATH_MODUS = 'payment/mnpy/modus';
    const XML_PATH_ETHADDRESS = 'payment/mnpy/ethaddress';
    const XML_PATH_FEE = 'payment/mnpy/fee';
    const XML_PATH_STORENAME = 'general/store_information/name';
    const XML_PATH_CURRENCIES = 'payment/mnpy/currencies';

    /** @var Context */
    protected $context;

    /**@var StoreManagerInterface */
    protected $storeManager;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->storeManager = $storeManager;
    }

    /**
     * @return int
     */
    public function getStoreId(): int
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @param $path
     * @param int $storeId
     * @return string|null
     */
    protected function getStoreConfig($path, $storeId = 0)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function hasApiKey($storeId = null): bool
    {
        return !is_null($this->getApiKey($storeId));
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getApiKey($storeId = null)
    {
        return $this->getStoreConfig(self::XML_PATH_APIKEY, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function hasApiSecretKey($storeId = null): bool
    {
        return !is_null($this->getApiSecretKey($this->getStoreId()));
    }

    /**
     * @param $storeId
     * @return string
     */
    public function getApiSecretKey($storeId = nul)
    {
        return $this->getStoreConfig(self::XML_PATH_APISECRET, $storeId);

    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getFullApiKey($storeId = null)
    {
        return $this->getModus() . '_' . $this->getApiKey($storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function getActive($storeId = null)
    {
        return $this->getStoreConfig(self::XML_PATH_ACTIVE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getStoreName($storeId = null)
    {
        return $this->getStoreConfig(self::XML_PATH_STORENAME, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getProcessingStatus($storeId = null)
    {
        return $this->getStoreConfig(self::XML_PATH_PROCESSING, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getPendingStatus($storeId = null)
    {
        return $this->getStoreConfig(self::XML_PATH_PENDING, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getModus($storeId = null)
    {
        return $this->getStoreConfig(self::XML_PATH_MODUS, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getEthAddress($storeId = null)
    {
        return $this->getStoreConfig(self::XML_PATH_ETHADDRESS, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return float
     */
    public function getFee($storeId = null)
    {
        return $this->getStoreConfig(self::XML_PATH_FEE, $storeId);
    }

    /**
     * @param $orderId
     * @return string
     */
    public function getRedirectUrl($orderId)
    {
        return $this->getUrlBuilder()->getUrl('mnpy/checkout/success/') . '?orderid=' . $orderId;
    }

    /**
     * @return bool
     */
    public function hasSufficientalance(): bool
    {
        return true;
    }

    /**
     * @param $currency
     * @param int|null $storeId
     * @return bool
     */
    public function isAllowedCurrency($currency, $storeId = null): bool
    {
        /**
         * Todo: implement API
         */
        $supported = ['USD', 'EUR', 'GBP'];

        return in_array($currency, $supported);
    }

    /**
     * @param $storeId
     * @return array
     */
    public function getEnabledCurrencies($storeId): array
    {
        return explode(',', $this->getStoreConfig(self::XML_PATH_CURRENCIES, $storeId));
    }

    /**
     * @return mixed
     */
    public function getCallbackUrl()
    {
        if ($this->getModus($this->getStoreId()) === 'live') {
            return $this->getUrlBuilder()->getUrl('mnpy/checkout/webhook/');
        }
        return null;
    }

    /**
     * @return \Magento\Framework\UrlInterface
     */
    private function getUrlBuilder()
    {
        return $this->context->getUrlBuilder();
    }
}
