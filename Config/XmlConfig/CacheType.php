<?php declare(strict_types=1);

namespace AdPage\GTM\Config\XmlConfig;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;
use Magento\Framework\Config\CacheInterface;

class CacheType extends TagScope implements CacheInterface
{
    public const TYPE_IDENTIFIER = 'data_layer';
    public const CACHE_TAG = 'DATA_LAYER';

    /**
     * @param FrontendPool $cacheFrontendPool
     */
    public function __construct(FrontendPool $cacheFrontendPool)
    {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}
