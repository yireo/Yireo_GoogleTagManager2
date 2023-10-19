<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Processor;

use AdPage\GTM\Api\Data\ProcessorInterface;
use AdPage\GTM\Config\XmlConfig;

class Base implements ProcessorInterface
{
    private XmlConfig $xmlConfig;

    public function __construct(
        XmlConfig $xmlConfig
    ) {
        $this->xmlConfig = $xmlConfig;
    }

    public function process(array $data): array
    {
        $default = $this->xmlConfig->getDefault();
        return array_merge($data, $default);
    }
}
