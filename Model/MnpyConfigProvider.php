<?php

namespace Mnpy\Magento2\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Mnpy\Magento2\Helper\Data as MnpyHelper;
use Mnpy\Magento2\Model\Config\Source\Tokens;
use MNPY\SDK\Token;

/**
 * Class MnpyConfigProvider
 *
 * @package Mnpy\Magento2\Model
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class MnpyConfigProvider implements ConfigProviderInterface
{
    const CODE = 'mnpy';

    /** @var \Magento\Payment\Model\MethodInterface */
    protected $method;

    /** @var MnpyHelper */
    protected $mnpyHelper;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /**
     * MnpyConfigProvider constructor.
     *
     * @param Data $paymentHelper
     * @param StoreManagerInterface $storeManager
     * @param MnpyHelper $mnpyHelper
     */
    public function __construct(
        Data $paymentHelper,
        StoreManagerInterface $storeManager,
        MnpyHelper $mnpyHelper
    ) {
        $this->storeManager = $storeManager;
        $this->mnpyHelper = $mnpyHelper;
        $this->method = $paymentHelper->getMethodInstance(self::CODE);
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                self::CODE => [
                    'tokens' => $this->getTokens(),
                ]
            ]
        ];
    }

    /**
     * Get enabled tokens
     *
     * @return array
     */
    public function getTokens(): array
    {
        $tokenObject = new Token(
            $this->mnpyHelper->getFullApiKey($this->storeManager->getStore()->getId())
        );

        $enabledTokens = $this->mnpyHelper->getEnabledCurrencies($this->storeManager->getStore()->getId());

        $tokensArray = [];
        try {
            foreach ($tokenObject->all() as $key => $token) {
                if (array_search($token->symbol, $enabledTokens) !== false) {
                    $tokensArray[$key]['id'] = $token->symbol;
                    $tokensArray[$key]['name'] = $token->name;
                }
            }

        } catch (\Exception $e) {
            return [];
        }

        return $tokensArray;
    }
}
