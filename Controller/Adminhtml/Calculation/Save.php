<?php

declare(strict_types=1);

namespace Lindenvalley\Calculation\Controller\Adminhtml\Calculation;

use Lindenvalley\Calculation\Model\Config;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Cache\Type\Config as CacheTypeConfig;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeList;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface as StoreScope;

class Save implements ActionInterface
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * @var RedirectFactory
     */
    private RedirectFactory $redirectFactory;

    /**
     * @var Json
     */
    private Json $serializer;

    /**
     * @var CacheTypeList
     */
    private CacheTypeList $cacheTypeList;

    /**
     * @var ReinitableConfigInterface
     */
    private ReinitableConfigInterface $reinitableConfig;

    /**
     * @param RequestInterface $request
     * @param ConfigInterface $config
     * @param RedirectFactory $redirectFactory
     * @param Json $serializer
     * @param CacheTypeList $cacheTypeList
     * @param ReinitableConfigInterface $reinitableConfig
     */
    public function __construct(
        RequestInterface $request,
        ConfigInterface $config,
        RedirectFactory $redirectFactory,
        Json $serializer,
        CacheTypeList $cacheTypeList,
        ReinitableConfigInterface $reinitableConfig
    ) {
        $this->request = $request;
        $this->config = $config;
        $this->redirectFactory = $redirectFactory;
        $this->serializer = $serializer;
        $this->cacheTypeList = $cacheTypeList;
        $this->reinitableConfig = $reinitableConfig;
    }

    /**
     * @inheirtDoc
     */
    public function execute(): ResultInterface
    {
        $params = $this->request->getParams();
        $storeId = (int)$params['store_id'] ?? null;
        $config = $params['config'] ?? [];
        $countries = $params['europe_countries'] ?? [];
        $cronExpression = $params['cron_expression'];
        $canRound = $params['can_round'];

        $this->saveConfig(Config::CALCULATION_GENERAL_CONFIG, $this->serializer->serialize($config), $storeId);
        $this->saveConfig(Config::CALCULATION_GENERAL_EUROPE_COUNTRIES, implode(',', $countries), $storeId);
        $this->saveConfig(Config::CALCULATION_GENERAL_CAN_ROUND, $canRound, $storeId);
        $this->saveConfig(Config::CALCULATION_GENERAL_CRON_EXPRESSION, $cronExpression, $storeId);

        $this->reinitableConfig->reinit();
        $this->cacheTypeList->invalidate(CacheTypeConfig::TYPE_IDENTIFIER);

        return $this->redirectFactory->create()->setPath(
            '*/*/config',
            $storeId ? ['store' => $storeId] : []
        );
    }

    /**
     * Save config by given path
     * @param string $path
     * @param string $value
     * @param int $storeId
     */
    private function saveConfig(string $path, string $value, int $storeId): void
    {
        $this->config->saveConfig(
            $path,
            $value,
            $storeId ? StoreScope::SCOPE_STORES : ScopeInterface::SCOPE_DEFAULT,
            $storeId
        );
    }
}
