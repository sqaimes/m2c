<?php

declare(strict_types=1);

namespace Lindenvalley\Calculation\Model\Source;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Country implements OptionSourceInterface
{
    /**
     * @var AttributeRepositoryInterface
     */
    private AttributeRepositoryInterface $attributeRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var array
     */
    private array $options = [];

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        LoggerInterface $logger
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->logger = $logger;
    }

    /**
     * @inheirtDoc
     */
    public function toOptionArray(): array
    {
        if (empty($this->options)) {
            try {
                $attribute = $this->attributeRepository->get(
                    ProductAttributeInterface::ENTITY_TYPE_CODE,
                    'country_of_manufacture'
                );
                $options = $attribute->getOptions();
                foreach ($options as $option) {
                    if ($option->getValue()) {
                        $this->options[] = [
                            'value' => $option->getValue(),
                            'label' => $option->getLabel(),
                        ];
                    }
                }
            } catch (LocalizedException $e) {
                $this->logger->error("Can't get attribute country_of_manufacture. Message: " . $e->getMessage());
                $this->options = [];
            }
        }

        return $this->options;
    }
}
