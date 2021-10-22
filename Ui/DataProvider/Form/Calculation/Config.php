<?php

declare(strict_types=1);

namespace Lindenvalley\Calculation\Ui\DataProvider\Form\Calculation;

use Lindenvalley\Calculation\Model\Config as ConfigModel;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\Store\Model\ScopeInterface;

class Config extends DataProvider
{
    /**
     * @var ConfigModel
     */
    private ConfigModel $config;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param ConfigModel $config
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        ConfigModel $config,
        array $meta = [],
        array $data = []
    ) {
        $this->config = $config;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
    }

    /**
     * @inheirtDoc
     */
    public function getData(): array
    {
        $data = [];
        $storeId = (int)$this->request->getParam('store');
        $scope = $storeId ? ScopeInterface::SCOPE_STORES : \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT;
        $data['config'] = $this->config->getFeeConfiguration($scope, $storeId);
        $data['store_id'] = $storeId;
        $data['europe_countries'] = $this->config->getEuropeCountries($scope, $storeId);
        $data['cron_expression'] = $this->config->getCronExpression($scope, $storeId);
        $data['can_round'] = (int)$this->config->canRound($scope, $storeId);

        return $data;
    }
}
