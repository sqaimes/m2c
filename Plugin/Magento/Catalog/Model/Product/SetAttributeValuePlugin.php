<?php

declare(strict_types=1);

namespace Lindenvalley\Calculation\Plugin\Magento\Catalog\Model\Product;

use Lindenvalley\Calculation\Model\Config;
use Lindenvalley\Calculation\Service\Calculation;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

class SetAttributeValuePlugin
{
    /**
     * @var Calculation
     */
    private Calculation $calculation;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param Config $config
     * @param Calculation $calculation
     */
    public function __construct(
        Config $config,
        Calculation $calculation
    ) {
        $this->config = $config;
        $this->calculation = $calculation;
    }

    /**
     * Calculate co2 fee if available
     *
     * @param Product $product
     *
     * @return array
     * @throws LocalizedException
     */
    public function beforeSave(Product $product): array
    {
        if (!$this->config->isEnabled(ScopeInterface::SCOPE_STORES, (int)$product->getStoreId())) {
            return [];
        }

        $this->calculation->calculate($product);

        return [];
    }
}
