<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag;

use Yireo\GoogleTagManager2\Config\Config;

/**
 * @see https://developers.google.com/tag-platform/tag-manager/api/v1/reference/accounts/containers/tags
 */
class AccountId implements AddTagInterface
{
    private Config $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    public function addData(): string
    {
        return $this->config->getId();
    }
}
