<?php

declare(strict_types=1);

namespace Lindenvalley\Calculation\Model;

use InvalidArgumentException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /**
     * The configuration path for is enabled
     */
    public const CALCULATION_GENERAL_IS_ENABLED = 'calculation/general/is_enabled';

    /**
     * The configuration path for calculation config
     */
    public const CALCULATION_GENERAL_CONFIG = 'calculation/general/config';

    /**
     * The configuration path for europe countries
     */
    public const CALCULATION_GENERAL_EUROPE_COUNTRIES = 'calculation/general/europe_countries';

    /**
     * The configuration path for can round checkbox
     */
    public const CALCULATION_GENERAL_CAN_ROUND = 'calculation/general/can_round';

    /**
     * The configuration path for cron expression
     */
    public const CALCULATION_GENERAL_CRON_EXPRESSION = 'calculation/general/cron_expression';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var Json
     */
    private Json $serializer;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $serializer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
    }

    /**
     * Check module status
     *
     * @param string $scopeType
     * @param int|null $scopeCode
     *
     * @return bool
     */
    public function isEnabled(
        string $scopeType = ScopeInterface::SCOPE_STORES,
        int $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::CALCULATION_GENERAL_IS_ENABLED, $scopeType, $scopeCode);
    }

    /**
     * Return array with configuration data
     *
     * @param string $scopeType
     * @param int|null $scopeCode
     *
     * @return array
     */
    public function getFeeConfiguration(
        string $scopeType = ScopeInterface::SCOPE_STORES,
        int $scopeCode = null
    ): array {
        $config = $this->scopeConfig->getValue(self::CALCULATION_GENERAL_CONFIG, $scopeType, $scopeCode);
        try {
            $config = $this->serializer->unserialize($config);
        } catch (InvalidArgumentException $e) {
            $config = [];
        }

        return $config;
    }

    /**
     * Return array selected europe countries
     *
     * @param string $scopeType
     * @param int|null $scopeCode
     *
     * @return array
     */
    public function getEuropeCountries(
        string $scopeType = ScopeInterface::SCOPE_STORES,
        int $scopeCode = null
    ): array {
        $countries = (string)$this->scopeConfig->getValue(self::CALCULATION_GENERAL_EUROPE_COUNTRIES, $scopeType, $scopeCode);

        return explode(',', $countries);
    }

    /**
     * Return array selected europe countries
     *
     * @param string $scopeType
     * @param int|null $scopeCode
     *
     * @return bool
     */
    public function canRound(
        string $scopeType = ScopeInterface::SCOPE_STORES,
        int $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::CALCULATION_GENERAL_CAN_ROUND, $scopeType, $scopeCode);
    }

    /**
     * Return cron expression
     *
     * @param string $scopeType
     * @param int|null $scopeCode
     *
     * @return string
     */
    public function getCronExpression(
        string $scopeType = ScopeInterface::SCOPE_STORES,
        int $scopeCode = null
    ): string {
        return (string)$this->scopeConfig->getValue(self::CALCULATION_GENERAL_CRON_EXPRESSION, $scopeType, $scopeCode);
    }
}
