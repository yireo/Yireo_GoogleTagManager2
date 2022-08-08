<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag;

use Yireo\GoogleTagManager2\Config\Config;

class Event implements AddTagInterface
{
    private Config $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    public function addData()
    {
        return null;
    }
}
