<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ViewModel;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use Yireo\GoogleTagManager2\DataLayer\TagParser;
use Yireo\GoogleTagManager2\DataLayer\Processor\ProcessorInterface;

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
        $block = $this->getDataLayerBlock();
        if (empty($block)) {
            return [];
        }

        $data = (array)$block->getData('data_layer');
        $processors = (array)$block->getData('data_layer_processors');
        $processors = array_merge($this->processors, $processors);

        return $this->variableParser->parse($data, $processors);
    }

    /**
     * @return string
     */
    public function getDataLayerAsJson(): string
    {
        return $this->toJson($this->getDataLayer());
    }

    /**
     * @return array
     */
    public function getDataLayerEvents(): array
    {
        $block = $this->getDataLayerBlock();
        if (empty($block)) {
            return [];
        }

        $data = (array)$block->getData('data_layer_events');
        $processors = (array)$block->getData('data_layer_processors');

        return $this->variableParser->parse($data, $processors);
    }

    /**
     * @return string[]
     */
    public function getDataLayerEventsAsJsonChunks(): array
    {
        $jsonChunks = [];
        foreach ($this->getDataLayerEvents() as $dataLayerEvent) {
            $jsonChunks[] = $this->toJson($dataLayerEvent);
        }

        return $jsonChunks;
    }

    /**
     * @return string
     */
    public function toJson(array $data): string
    {
        return $this->serializer->serialize($data);
    }

    /**
     * @return BlockInterface
     */
    private function getDataLayerBlock(): BlockInterface
    {
        return $this->layout->getBlock('yireo_googletagmanager2.data-layer');
    }
}
