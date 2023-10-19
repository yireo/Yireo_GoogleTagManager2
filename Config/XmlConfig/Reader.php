<?php declare(strict_types=1);

namespace AdPage\GTM\Config\XmlConfig;

use Magento\Framework\Config\Reader\Filesystem;

class Reader extends Filesystem
{
    protected $_idAttributes = ['/data_layer/type' => 'name'];
}
