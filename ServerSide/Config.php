<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Yireo\GoogleTagManager2\ServerSide\Config\Source\Destination;
use Yireo\GoogleTagManager2\ServerSide\Config\Source\DispatchMode;
use Yireo\GoogleTagManager2\ServerSide\Config\Source\PurchaseTrigger;

class Config
{
    private const TRIM_CHARACTERS = " \n\r\t\v\0";
    public const GA4_ENDPOINT = 'https://www.google-analytics.com';
    private const PREFIX = 'googletagmanager2/serverside_tracking/';

    private ScopeConfigInterface $scopeConfig;
    private EncryptorInterface $encryptor;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    public function isEnabled(?int $storeId = null): bool
    {
        if (false === $this->isFlag(self::PREFIX . 'enabled', $storeId)) {
            return false;
        }

        return $this->getMeasurementId($storeId) !== '' && $this->getApiSecret($storeId) !== '';
    }

    public function getDestination(?int $storeId = null): string
    {
        return $this->getValue('destination', $storeId) === Destination::SGTM ? Destination::SGTM : Destination::GA4;
    }

    public function getMeasurementId(?int $storeId = null): string
    {
        return trim($this->getValue('measurement_id', $storeId), self::TRIM_CHARACTERS);
    }

    public function getApiSecret(?int $storeId = null): string
    {
        $value = $this->getValue('api_secret', $storeId);
        if ($value === '') {
            return '';
        }

        return trim((string)$this->encryptor->decrypt($value), self::TRIM_CHARACTERS);
    }

    public function getSgtmUrl(?int $storeId = null): string
    {
        return rtrim(trim($this->getValue('sgtm_url', $storeId), self::TRIM_CHARACTERS), '/');
    }

    public function getEndpointUrl(?int $storeId = null, bool $validation = false): string
    {
        $baseUrl = self::GA4_ENDPOINT;
        if ($this->getDestination($storeId) === Destination::SGTM && $this->getSgtmUrl($storeId) !== '') {
            $baseUrl = $this->getSgtmUrl($storeId);
        }

        return $baseUrl . ($validation ? '/debug/mp/collect' : '/mp/collect');
    }

    public function getEvents(?int $storeId = null): array
    {
        return $this->toList($this->getValue('events', $storeId));
    }

    public function isEventEnabled(string $eventName, ?int $storeId = null): bool
    {
        return $this->isEnabled($storeId) && in_array($eventName, $this->getEvents($storeId), true);
    }

    public function getPurchaseTrigger(?int $storeId = null): string
    {
        $trigger = $this->getValue('purchase_trigger', $storeId);
        $allowed = [PurchaseTrigger::ORDER_PLACE, PurchaseTrigger::ORDER_STATE, PurchaseTrigger::INVOICE_PAY];
        return in_array($trigger, $allowed, true) ? $trigger : PurchaseTrigger::ORDER_PLACE;
    }

    public function getPurchaseOrderStates(?int $storeId = null): array
    {
        $value = (string)$this->scopeConfig->getValue(
            'googletagmanager2/settings/order_states_for_purchase_event',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $this->toList($value);
    }

    public function getDispatchMode(?int $storeId = null): string
    {
        return $this->getValue('dispatch_mode', $storeId) === DispatchMode::QUEUE
            ? DispatchMode::QUEUE
            : DispatchMode::CRON;
    }

    public function suppressBrowserPurchase(?int $storeId = null): bool
    {
        return $this->isEventEnabled('purchase', $storeId)
            && $this->isFlag(self::PREFIX . 'suppress_browser_purchase', $storeId);
    }

    public function requireConsent(?int $storeId = null): bool
    {
        return $this->isFlag('googletagmanager2/consent/enabled', $storeId)
            && $this->isFlag(self::PREFIX . 'require_consent', $storeId);
    }

    public function isValidationMode(?int $storeId = null): bool
    {
        return $this->isFlag(self::PREFIX . 'validation_mode', $storeId);
    }

    public function getMaxAttempts(): int
    {
        return max(1, (int)$this->getValue('max_attempts'));
    }

    public function getRetentionDays(): int
    {
        return max(1, (int)$this->getValue('retention_days'));
    }

    private function toList(string $value): array
    {
        $values = array_map(fn ($item) => trim($item, self::TRIM_CHARACTERS), explode(',', $value));
        return array_values(array_filter($values));
    }

    private function isFlag(string $path, ?int $storeId): bool
    {
        return $this->scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    private function getValue(string $key, ?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(self::PREFIX . $key, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
