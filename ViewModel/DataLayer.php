<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ViewModel;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\LayoutInterface;
use Yireo\GoogleTagManager2\Api\Data\ProcessorInterface;
use Yireo\GoogleTagManager2\Config\XmlConfig;
use Yireo\GoogleTagManager2\DataLayer\TagParser;
use Yireo\GoogleTagManager2\Exception\BlockNotFound;

class DataLayer implements ArgumentInterface
{
    private TagParser $variableParser;
    private LayoutInterface $layout;

    /**
     * @var ProcessorInterface[]
     */
    protected array $processors;
    private XmlConfig $xmlConfig;

    /**
     * @param TagParser $variableParser
     * @param LayoutInterface $layout
     * @param XmlConfig $xmlConfig
     * @param array $processors
     */
    public function __construct(
        TagParser $variableParser,
        LayoutInterface $layout,
        XmlConfig $xmlConfig,
        array $processors = []
    ) {
        $this->variableParser = $variableParser;
        $this->layout = $layout;
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
            if ($eventData === []) {
                unset($data[$eventId]);
                continue;
            }

            $data[$eventId] = array_merge($eventData, $this->xmlConfig->getEvent((string)$eventId));
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
            $jsonChunk = $this->encode($dataLayerEvent);
            if ($jsonChunk !== null) {
                $jsonChunks[] = $jsonChunk;
            }
        }

        return $jsonChunks;
    }

    /**
     * Encode data as JSON that is safe to print within an inline script, falling back to an empty object
     *
     * @param array $data
     * @return string
     */
    public function toJson(array $data): string
    {
        return $this->encode($data) ?? '{}';
    }

    /**
     * @param array $data
     * @return string|null
     */
    private function encode(array $data): ?string
    {
        try {
            return json_encode(
                $data,
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
                | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $jsonException) {
            return null;
        }
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
        $blockName = 'yireo_googletagmanager2.data-layer';
        $block = $this->layout->getBlock($blockName);
        if ($block instanceof AbstractBlock) {
            return $block;
        }

        throw new BlockNotFound('Block "' . $blockName . '" not found');
    }
}
