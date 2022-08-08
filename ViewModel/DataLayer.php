<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ViewModel;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\LayoutInterface;
use Yireo\GoogleTagManager2\DataLayer\TagParser;
use Yireo\GoogleTagManager2\DataLayerProcessor\ProcessorInterface;

class DataLayer implements ArgumentInterface
{
    private TagParser $variableParser;
    private LayoutInterface $layout;
    private SerializerInterface $serializer;

    /**
     * @var ProcessorInterface[]
     */
    protected array $processors;

    /**
     * @param TagParser $variableParser
     * @param LayoutInterface $layout
     * @param array $processors
     * @param SerializerInterface $serializer
     */
    public function __construct(
        TagParser $variableParser,
        LayoutInterface $layout,
        SerializerInterface $serializer,
        array $processors = []
    ) {
        $this->variableParser = $variableParser;
        $this->layout = $layout;
        $this->serializer = $serializer;
        $this->processors = $processors;
    }

    /**
     * @return array
     */
    public function getDataLayer(): array
    {
        $block = $this->layout->getBlock('yireo_googletagmanager2.data-layer');
        if (empty($block)) {
            return [];
        }

        $data = (array)$block->getData('data_layer');
        $processors = (array)$block->getData('processors');
        return $this->variableParser->parse($data, $processors);
    }

    /**
     * @return string
     */
    public function getDataLayerAsJson(): string
    {
        return $this->serializer->serialize($this->getDataLayer());
    }
}
