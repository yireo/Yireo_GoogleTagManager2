<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Tag;

use AdPage\GTM\Api\Data\TagInterface;
use AdPage\GTM\Config\Config;

/**
 * @see https://developers.google.com/tag-platform/tag-manager/api/v1/reference/accounts/containers/tags
 */
class AccountId implements TagInterface
{
    private Config $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    public function get(): string
    {
        return $this->config->getId();
    }
}
