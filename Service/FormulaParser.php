<?php

declare(strict_types=1);

namespace Lindenvalley\Calculation\Service;

use Lindenvalley\Calculation\Model\Config;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

class FormulaParser
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var AttributeRepositoryInterface
     */
    private AttributeRepositoryInterface $attributeRepository;

    /**
     * @var AttributeInterface[]
     */
    private array $attributes = [];

    /**
     * @param Config $config
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        Config $config,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->config = $config;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Replace formula conditions & attributes to values
     *
     * @param ProductInterface $product
     * @param string $formula
     *
     * @return string
     * @throws LocalizedException
     */
    public function parse(ProductInterface $product, string $formula): string
    {
        $this->parseConditions($product, $formula);
        $this->parseAttributes($product, $formula);

        return $formula;
    }

    /**
     * Replace conditions to values
     *
     * @param ProductInterface $product
     * @param string $formula
     *
     * @return void
     * @throws LocalizedException
     */
    private function parseConditions(ProductInterface $product, string &$formula): void
    {
        preg_match_all("!\[{{(\w+)}}=='(\w+)'\?(\w+):(\w+)]!", $formula, $matches);
        list ($conditions, $attributes, $attributesValue, $trueValues, $falseValues) = array_pad($matches, 5, []);
        foreach ($attributes as $key => $attribute) {
            $productValue = $product->getData($attribute);
            if (is_numeric($productValue)) {
                $productValue = $this->resolveOptionLabelValue($attribute, $productValue);
            }

            if (isset($attributesValue[$key]) && $productValue === $attributesValue[$key]) {
                $formula = str_replace($conditions[$key], $trueValues[$key], $formula);
            } else {
                $formula = str_replace($conditions[$key], $falseValues[$key], $formula);
            }
        }
    }

    /**
     * Return option label from option id
     *
     * @param string $attribute
     * @param string $value
     *
     * @return string
     * @throws LocalizedException
     */
    private function resolveOptionLabelValue(string $attribute, string $value): string
    {
        if (empty($this->attributes[$attribute])) {
            $this->attributes[$attribute] = $this->attributeRepository->get(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attribute
            );
        }

        $attribute = $this->attributes[$attribute];
        /** @var \Magento\Eav\Model\Entity\Attribute\Source\Table|\Magento\Eav\Model\Entity\Attribute\Source\SourceInterface $source */
        $source = $attribute->getSource();

        return (string)$source->getOptionText($value);
    }

    /**
     * Replace attributes to values
     *
     * @param ProductInterface $product
     * @param string $formula
     *
     * @return void
     */
    private function parseAttributes(ProductInterface $product, string &$formula): void
    {
        preg_match_all('!{{(\w+)}}!', $formula, $matches);
        list ($matches, $attributes) = array_pad($matches, 2, []);
        foreach ($attributes as $key => $attributeCode) {
            if ($attributeCode === 'is_europe') {
                $country = $product->getData('country_of_manufacture');
                $countries = $this->config->getEuropeCountries(
                    ScopeInterface::SCOPE_STORES,
                    (int)$product->getStoreId()
                );
                $value = (int)in_array($country, $countries);
            } else {
                $value = $product->getData($attributeCode);
            }

            $formula = str_replace($matches[$key], $value ?: 0, $formula);
        }
    }
}
