<?php declare(strict_types=1);

namespace Tagging\GTM\DataLayer\Tag;

use Tagging\GTM\Api\Data\TagInterface;
use Tagging\GTM\Config\Config;

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
