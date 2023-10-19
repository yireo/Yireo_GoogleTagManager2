<?php declare(strict_types=1);

namespace AdPage\GTM\ViewModel;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\LayoutInterface;
use AdPage\GTM\Api\Data\ProcessorInterface;
use AdPage\GTM\Config\XmlConfig;
use AdPage\GTM\DataLayer\TagParser;
use AdPage\GTM\Exception\BlockNotFound;

class DataLayer implements ArgumentInterface
{
    private TagParser $variableParser;
    private LayoutInterface $layout;
    private SerializerInterface $serializer;

    /**
     * @var ProcessorInterface[]
     */
    protected array $processors;
    private XmlConfig $xmlConfig;

    /**
     * @param TagParser $variableParser
     * @param LayoutInterface $layout
     * @param SerializerInterface $serializer
     * @param XmlConfig $xmlConfig
     * @param array $processors
     */
    public function __construct(
        TagParser $variableParser,
        LayoutInterface $layout,
        SerializerInterface $serializer,
        XmlConfig $xmlConfig,
        array $processors = []
    ) {
        $this->variableParser = $variableParser;
        $this->layout = $layout;
        $this->serializer = $serializer;
        $this->processors = $processors;
        $this->xmlConfig = $xmlConfig;
    }

    /**
     * @return array
     */
    public function getDataLayer(): array
    {
        try {
            $block = $this->getDataLayerBlock();
        } catch(BlockNotFound $blockNotFound) {
            return [];
        }

        $data = (array)$block->getData('data_layer');
        $processors = $this->getProcessors();
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
        try {
            $block = $this->getDataLayerBlock();
        } catch(BlockNotFound $blockNotFound) {
            return [];
        }

        $data = (array)$block->getData('data_layer_events');
        $processors = $this->getProcessors();

        $data = $this->variableParser->parse($data, $processors);

        foreach ($data as $eventId => $eventData) {
            $data[$eventId] = array_merge($eventData, $this->xmlConfig->getEvent($eventId));
        }

        return $data;
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
        return (string)$this->serializer->serialize($data);
    }

    /**
     * @return ProcessorInterface[]
     */
    private function getProcessors(): array
    {
        try {
            $block = $this->getDataLayerBlock();
        } catch(BlockNotFound $blockNotFound) {
            return [];
        }

        $processors = (array)$block->getData('data_layer_processors');
        return array_merge($this->processors, $processors);
    }

    /**
     * @return AbstractBlock
     * @throws BlockNotFound
     */
    private function getDataLayerBlock(): AbstractBlock
    {
        $blockName = 'AdPage_GTM.data-layer';
        $block = $this->layout->getBlock($blockName);
        if ($block instanceof AbstractBlock) {
            return $block;
        }

        throw new BlockNotFound('Block "' . $blockName . '" not found');
    }
}
