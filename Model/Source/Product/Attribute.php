<?php

declare(strict_types=1);

namespace Lindenvalley\Calculation\Model\Source\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Api\Search\SearchCriteriaFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\InputException;
use Psr\Log\LoggerInterface;

class Attribute implements OptionSourceInterface
{
    /**
     * @var AttributeRepositoryInterface
     */
    private AttributeRepositoryInterface $attributeRepository;

    /**
     * @var SearchCriteriaInterface
     */
    private $searchCriteria;

    /**
     * @var SortOrder
     */
    private SortOrder $sortOrder;

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
     * @param SortOrder $sortOrder
     * @param SearchCriteriaFactory $searchCriteria
     * @param LoggerInterface $logger
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        SortOrder $sortOrder,
        SearchCriteriaFactory $searchCriteria,
        LoggerInterface $logger
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->sortOrder = $sortOrder;
        $this->searchCriteria = $searchCriteria;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray(): array
    {
        if (!empty($this->options)) {
            return $this->options;
        }

        $searchCriteria = $this->searchCriteria->create();
        try {
            $sortOrder = $this->sortOrder->setField(AttributeInterface::FRONTEND_LABEL)
                ->setDirection(SortOrder::SORT_ASC);
            $searchCriteria->setSortOrders([$sortOrder]);
        } catch (InputException $e) {
            $this->logger->warning($e->getMessage());
        }

        $attributes = $this->attributeRepository->getList(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteria
        )->getItems();
        foreach ($attributes as $attribute) {
            $this->options[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getDefaultFrontendLabel()
            ];
        }

        return $this->options;
    }
}
