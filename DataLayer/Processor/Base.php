<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Processor;

use Yireo\GoogleTagManager2\Api\Data\ProcessorInterface;
use Yireo\GoogleTagManager2\Config\XmlConfig;

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
