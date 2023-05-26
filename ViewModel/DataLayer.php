<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ViewModel;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use Yireo\GoogleTagManager2\Api\Data\ProcessorInterface;
use Yireo\GoogleTagManager2\DataLayer\TagParser;
use Yireo\GoogleTagManager2\Exception\BlockNotFound;

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
     * @param SerializerInterface $serializer
     * @param array $processors
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
        $block = $this->getDataLayerBlock();
        $data = (array)$block->getData('data_layer_events');
        $processors = $this->getProcessors();
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
        return (string)$this->serializer->serialize($data);
    }

    /**
     * @return ProcessorInterface[]
     */
    private function getProcessors(): array
    {
        $block = $this->getDataLayerBlock();
        $processors = (array)$block->getData('data_layer_processors');
        return array_merge($this->processors, $processors);
    }

    /**
     * @return BlockInterface
     * @throws BlockNotFound
     */
    private function getDataLayerBlock(): BlockInterface
    {
        $blockName = 'yireo_googletagmanager2.data-layer';
        $block = $this->layout->getBlock($blockName);
        if ($block instanceof BlockInterface) {
            return $block;
        }

        throw new BlockNotFound('Block "' . $blockName . '" not found');
    }
}
