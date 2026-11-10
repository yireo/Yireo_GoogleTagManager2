<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Context;

use Magento\Framework\Stdlib\CookieManagerInterface;

class CookieReader
{
    private const TRIM_CHARACTERS = " \n\r\t\v\0";
    public const CONSENT_COOKIE = 'yireo_gtm2_consent';

    private CookieManagerInterface $cookieManager;

    public function __construct(
        CookieManagerInterface $cookieManager
    ) {
        $this->cookieManager = $cookieManager;
    }

    public function getClientId(): ?string
    {
        return $this->parseClientId((string)$this->cookieManager->getCookie('_ga'));
    }

    public function getSessionId(string $measurementId): ?string
    {
        $suffix = preg_replace('/^G-/', '', $measurementId);
        if ($suffix === '') {
            return null;
        }

        return $this->parseSessionId((string)$this->cookieManager->getCookie('_ga_' . $suffix));
    }

    public function getConsent(): ?array
    {
        return $this->parseConsent((string)$this->cookieManager->getCookie(self::CONSENT_COOKIE));
    }

    public function parseClientId(string $value): ?string
    {
        $parts = explode('.', trim($value, self::TRIM_CHARACTERS));
        if (count($parts) < 4) {
            return null;
        }

        $clientId = $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
        return preg_match('/^\d+\.\d+$/', $clientId) ? $clientId : null;
    }

    public function parseSessionId(string $value): ?string
    {
        $parts = explode('.', trim($value, self::TRIM_CHARACTERS));
        if (count($parts) < 3) {
            return null;
        }

        $sessionId = $parts[2];
        if (strpos($sessionId, 's') === 0) {
            $sessionId = substr(explode('$', $sessionId)[0], 1);
        }

        return preg_match('/^\d+$/', $sessionId) ? $sessionId : null;
    }

    public function parseConsent(string $value): ?array
    {
        if ($value === '') {
            return null;
        }

        $data = json_decode(rawurldecode($value), true);
        if (false === is_array($data)) {
            return null;
        }

        return [
            'analytics' => ($data['analytics'] ?? false) === true,
            'marketing' => ($data['marketing'] ?? false) === true,
            'preferences' => ($data['preferences'] ?? false) === true,
        ];
    }
}
