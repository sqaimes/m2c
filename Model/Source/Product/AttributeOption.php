<?php

declare(strict_types=1);

namespace Lindenvalley\Calculation\Model\Source\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class AttributeOption implements OptionSourceInterface
{
    /**
     * @var AttributeOptionManagementInterface
     */
    private AttributeOptionManagementInterface $attributeOptionManagement;

    /**
     * @var Attribute
     */
    private Attribute $attributeSource;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var array
     */
    private array $options = [];

    /**
     * @param AttributeOptionManagementInterface $attributeOptionManagement
     * @param Attribute $attributeSource
     * @param LoggerInterface $logger
     */
    public function __construct(
        AttributeOptionManagementInterface $attributeOptionManagement,
        Attribute $attributeSource,
        LoggerInterface $logger
    ) {
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->attributeSource = $attributeSource;
        $this->logger = $logger;
    }

    /**
     * @inheirtDoc
     */
    public function toOptionArray(): array
    {
        if (empty($this->options)) {
            foreach ($this->attributeSource->toOptionArray() as $item) {
                $attributeCode = $item['value'];
                try {
                    $options = $this->attributeOptionManagement->getItems(
                        ProductAttributeInterface::ENTITY_TYPE_CODE,
                        $attributeCode
                    );
                } catch (LocalizedException $e) {
                    $this->logger->error(
                        "Can't get options for attribute $attributeCode. Message: " . $e->getMessage()
                    );
                    $options = [];
                }

                foreach ($options as $option) {
                    $this->options[] = [
                        'value' => $option->getValue(),
                        'label' => __($option->getLabel()),
                        'attribute_code' => $attributeCode,
                    ];
                }
            }
        }

        return $this->options;
    }
}
