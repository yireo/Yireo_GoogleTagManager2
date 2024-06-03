<?php declare(strict_types=1);

namespace Tagging\GTM\DataLayer\Processor;

use Tagging\GTM\Api\Data\ProcessorInterface;
use Tagging\GTM\Config\XmlConfig;

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
