<?php

declare(strict_types=1);

namespace Lindenvalley\Calculation\Model\Source\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;

class AttributeSetId implements OptionSourceInterface
{
    /**
     * @var AttributeSetRepositoryInterface
     */
    private AttributeSetRepositoryInterface $attributeSetRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory;

    /**
     * @var array
     */
    private array $options = [];

    /**
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    public function __construct(
        AttributeSetRepositoryInterface $attributeSetRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {
        $this->attributeSetRepository = $attributeSetRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    /**
     * @inheirtDoc
     */
    public function toOptionArray(): array
    {
        if (empty($this->options)) {
            $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create()->addFilter(
                'entity_type_code',
                Product::ENTITY
            );
            $attributeSetList = $this->attributeSetRepository->getList($searchCriteriaBuilder->create());
            foreach ($attributeSetList->getItems() as $attributeSet) {
                $this->options[] = [
                    'value' => $attributeSet->getAttributeSetId(),
                    'label' => __($attributeSet->getAttributeSetName()),
                ];
            }
        }

        return $this->options;
    }
}
