<?php

namespace Mnpy\Magento2\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mnpy\Magento2\Helper\Data;
use MNPY\SDK\Token;

/**
 * Class Modus
 *
 * @package Mnpy\Magento2\Model\Config\Source
 */
class Tokens implements ArrayInterface
{
    const LIVE = 'live_';

    /** @var MnpyHelper*/
    protected $mnpyhelper;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /**
     * Tokens constructor.
     * @param Data $mnpyHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(Data $mnpyHelper, StoreManagerInterface $storeManager)
    {
        $this->mnpyHelper = $mnpyHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * Return tokens
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        /**
         * Check if API key is set, otherwise this will fail
         */
        if (is_null($this->mnpyHelper->getApiKey($this->storeManager->getStore()->getId()))) {
            return [];
        }

        $tokensArray = [];

        try {
            $tokenObject = new Token(
                $this->mnpyHelper->getFullApiKey($this->storeManager->getStore()->getId())
            );
            foreach ($tokenObject->all() as $key => $token) {
                $tokensArray[$key]['value'] = $token->symbol;
                $tokensArray[$key]['label'] = $token->name;
            }
        } catch (\Exception $e) {
            return [];
        }

        return $tokensArray;
    }
}
