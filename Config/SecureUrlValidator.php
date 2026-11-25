<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Config;

class SecureUrlValidator
{
    public const URL_PATTERN = '#^https://([a-z0-9.\-]+)(?::([0-9]{1,5}))?(/[a-z0-9._~%!$&()*+,;=:@/\-]*)?$#iD';
    public const HOST_PATTERN = '/^(?:[a-z0-9](?:[a-z0-9\-]{0,61}[a-z0-9])?\.)+[a-z](?:[a-z0-9\-]{0,61}[a-z0-9])?$/iD';
    private const RESERVED_TLDS = ['localhost', 'local', 'localdomain', 'internal', 'arpa'];

    public function isValid(string $url): bool
    {
        if (false === (bool)preg_match(self::URL_PATTERN, $url, $matches)) {
            return false;
        }

        $port = $matches[2] ?? '';
        if ($port !== '' && ((int)$port < 1 || (int)$port > 65535)) {
            return false;
        }

        return $this->isValidHost(strtolower($matches[1]));
    }

    private function isValidHost(string $host): bool
    {
        if (strlen($host) > 253 || false === (bool)preg_match(self::HOST_PATTERN, $host)) {
            return false;
        }

        if (false !== filter_var($host, FILTER_VALIDATE_IP)) {
            return false;
        }

        $labels = explode('.', $host);

        return false === in_array(end($labels), self::RESERVED_TLDS, true);
    }
}
