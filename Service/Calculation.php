<?php

declare(strict_types=1);

namespace Lindenvalley\Calculation\Service;

use Exception;
use Lindenvalley\Calculation\Model\Config;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use NXP\MathExecutor;

class Calculation
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var MathExecutor
     */
    private MathExecutor $mathExecutor;

    /**
     * @var FormulaParser
     */
    private FormulaParser $formulaParser;

    /**
     * @var string|null
     */
    private ?string $currentFormula;

    /**
     * @var string|null
     */
    private ?string $currentAttribute;

    /**
     * @param Config $config
     * @param MathExecutor $mathExecutor
     * @param FormulaParser $formulaParser
     */
    public function __construct(
        Config $config,
        MathExecutor $mathExecutor,
        FormulaParser $formulaParser
    ) {
        $this->config = $config;
        $this->mathExecutor = $mathExecutor;
        $this->formulaParser = $formulaParser;
    }

    /**
     * Calculate attribute for product
     *
     * @param ProductInterface $product
     * @param array $config
     *
     * @return void
     * @throws LocalizedException
     */
    public function calculate(ProductInterface $product, array $config = []): void
    {
        if ($this->canCalculate($product, $config)) {
            try {
                $expression = $this->formulaParser->parse($product, $this->currentFormula);
                $value = $this->mathExecutor->execute($expression);
                if ($this->config->canRound(ScopeInterface::SCOPE_STORES, (int)$product->getStoreId())) {
                    $value = round($value, 2);
                }

                $product->setData($this->currentAttribute, $value);
            } catch (Exception $e) {
                throw new LocalizedException(__("Can't calculate. Message: " . $e->getMessage()));
            }
        }
    }

    /**
     * Check allows to calculate
     *
     * @param ProductInterface $product
     * @param array $config
     *
     * @return bool
     */
    private function canCalculate(ProductInterface $product, array $config = []): bool
    {
        if (!empty($config)) {
            $configs = [$config];
        } else {
            $configs = $this->config->getFeeConfiguration(
                ScopeInterface::SCOPE_STORES,
                (int)$product->getStoreId()
            );
        }

        $result = false;
        foreach ($configs as $config) {
            $attributeSetId = $config['attribute_set_id'] ?? null;
            $attributeCode = $config['attribute_code'] ?? null;
            $attributeValue = $config['attribute_option'] ?? null;
            $attribute = $config['attribute'] ?? null;
            $formula = $config['formula'] ?? null;
            $productAttributeValue = $product->getData($attributeCode);
            if ((int)$attributeSetId === (int)$product->getAttributeSetId()) {
                $result = true;
            } elseif ($productAttributeValue === $attributeValue) {
                $result = true;
            }

            if ($result && $formula) {
                $this->currentFormula = $formula;
                $this->currentAttribute = $attribute;
                break;
            }
        }

        return $result;
    }
}
