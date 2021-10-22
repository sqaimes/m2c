<?php

declare(strict_types=1);

namespace Lindenvalley\Calculation\Cron;

use Exception;
use Lindenvalley\Calculation\Model\Config;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\ProductFactory as ProductResourceFactory;
use Lindenvalley\Calculation\Service\Calculation;
use Psr\Log\LoggerInterface;

class Calculate
{
    /**
     * Collection page size
     */
    private const PAGE_SIZE = 500;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var Calculation
     */
    private Calculation $calculation;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var ProductResourceFactory
     */
    private ProductResourceFactory $productResourceFactory;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Calculation $calculation
     * @param Config $config
     * @param ProductResourceFactory $productResourceFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Calculation $calculation,
        Config $config,
        ProductResourceFactory $productResourceFactory,
        LoggerInterface $logger
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->calculation = $calculation;
        $this->config = $config;
        $this->productResourceFactory = $productResourceFactory;
        $this->logger = $logger;
    }

    /**
     * Calculate fee for products which doesn't have them
     *
     * @return void
     */
    public function execute(): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $currentPage = 1;
        $productResource = $this->productResourceFactory->create();
        $configs = $this->config->getFeeConfiguration();
        foreach ($configs as $config) {
            $attribute = $config['attribute'];
            $products = $this->collectionFactory->create()
                ->addFieldToFilter($attribute, ['null' => true])
                ->addAttributeToSelect(['*'])
                ->setCurPage($currentPage)
                ->setPageSize(self::PAGE_SIZE);
            $lastPage = $products->getLastPageNumber();
            while ($currentPage <= $lastPage) {
                $products->setCurPage($currentPage);
                foreach ($products as $product) {
                    try {
                        $this->calculation->calculate($product, $config);
                        $productResource->saveAttribute($product, $attribute);
                    } catch (Exception $e) {
                        $this->logger->error(
                            "Can't calculate attribute value for product: " . $product->getId() . " Message: " . $e->getMessage()
                        );
                    }
                }

                $currentPage++;
                $products->clear();
            }
        }
    }
}
