<?php

declare(strict_types=1);

namespace Tagging\GTM\Model\Collector;

use Magento\Csp\Api\PolicyCollectorInterface;
use Magento\Csp\Model\Policy\FetchPolicy;
use Tagging\GTM\Config\Config;

class DynamicTaggingCollector implements PolicyCollectorInterface
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function collect(array $defaultPolicies = []): array
    {
        try {
            if (!$this->config->isEnabled()) {
                return $defaultPolicies;
            }

            if (!$this->config->isPlacedByPlugin()) {
                return $defaultPolicies;
            }   

            $gtmUrl = $this->config->getGoogleTagmanagerUrl();

            if (empty($gtmUrl)) {
                return $defaultPolicies;
            }

            if (!preg_match('/^https?:\/\//', $gtmUrl)) {
                $gtmUrl = 'https://' . $gtmUrl;
            }

            $parsedUrl = parse_url($gtmUrl);

            if (!$parsedUrl || !isset($parsedUrl['host'])) {
                return $defaultPolicies;
            }

            $domain = $parsedUrl['host'];
            $protocol = $parsedUrl['scheme'] ?? 'https';

            $taggingUrl = $protocol . '://' . $domain;

            $policies = [
                new FetchPolicy(
                    'script-src',
                    false,
                    [$taggingUrl],
                    [$protocol]
                ),
                new FetchPolicy(
                    'connect-src',
                    false,
                    [$taggingUrl],
                    [$protocol]
                )
            ];

           return array_merge($defaultPolicies, $policies);

        } catch (\Exception $e) {
            return $defaultPolicies;
        }
    }
}