<?php

namespace Mnpy\Magento2\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Modus
 *
 * @package Mnpy\Magento2\Model\Config\Source
 */
class Modus implements ArrayInterface
{
    /**
     * Return staging/live modus
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ["value" => "staging", "label" => "Staging"],
            ["value" => "live", "label" => "Live"],
        ];
    }
}
