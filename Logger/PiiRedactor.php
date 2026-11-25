<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Logger;

class PiiRedactor
{
    public const REDACTED = '[redacted]';
    private const KEYS = ['client_id', 'user_id', 'session_id', 'user_data', 'api_secret'];
    private const KEY_PREFIXES = ['sha256_', 'customer'];

    /**
     * @param mixed $data
     * @return mixed
     */
    public function redact($data)
    {
        if (is_object($data)) {
            return '(object) ' . get_class($data);
        }

        if (false === is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            $data[$key] = $this->isPiiKey((string)$key) ? self::REDACTED : $this->redact($value);
        }

        return $data;
    }

    private function isPiiKey(string $key): bool
    {
        $key = strtolower($key);
        if (in_array($key, self::KEYS, true) || str_contains($key, 'email')) {
            return true;
        }

        foreach (self::KEY_PREFIXES as $prefix) {
            if (str_starts_with($key, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
