<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayerProcessor;

class CompositeProcessor implements ProcessorInterface
{
    /**
     * @var array|ProcessorInterface[]
     */
    protected $providers;

    /**
     * @param ProcessorInterface[] $providers
     */
    public function __construct(
        array $providers = []
    ) {
        $this->providers = $providers;
    }

    /**
     * @inheritDoc
     */
    public function process(array $data): array
    {
        $data = [];

        foreach ($this->providers as $provider) {
            $data = array_merge_recursive($data, $provider->getData());
        }

        return $data;
    }
}
