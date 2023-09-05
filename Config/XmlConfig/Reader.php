<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Config\XmlConfig;

use Magento\Framework\Config\Reader\Filesystem;

class Reader extends Filesystem
{
    protected $_idAttributes = ['/data_layer/type' => 'name'];
}
