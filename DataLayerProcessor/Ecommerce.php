<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayerProcessor;

use Yireo\GoogleTagManager2\Config\Config;

class Ecommerce implements ProcessorInterface
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function process(array $data): array
    {
        if (!$this->config->getIsDynamicRemarketingEnabled()) {
            return [];
        }

        return [
            'event' => $this->config->getDynamicRemarketingEventName(),
            'google_tag_params' => [
                'ecomm_pagetype' => $data['page']['type']
            ]
        ];
    }
}