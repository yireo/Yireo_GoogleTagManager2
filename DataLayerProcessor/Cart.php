<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayerProcessor;

use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Helper\Cart as CartHelper;

class Cart implements ProcessorInterface
{

    /**
     * @var CartHelper
     */
    protected $cartHelper;

    /**
     * @var Config
     */
    protected $config;

    /**
     * CartProvider constructor.
     * @param CartHelper $cartHelper
     * @param Config $config
     */
    public function __construct(
        CartHelper $cartHelper,
        Config $config
    ) {
        $this->cartHelper = $cartHelper;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function process(array $data): array
    {
        if ($this->config->isDataLayerTrackCartAsCheckoutStep()) {
            return $this->cartHelper->getData($this->config->getDataLayerCartStep());
        }

        return [];
    }
}
