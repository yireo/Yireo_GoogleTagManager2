<?php declare(strict_types=1);

namespace AdPage\GTM\Config\XmlConfig;

use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;

class SchemaLocator implements SchemaLocatorInterface
{
    /**
     * @var Reader
     */
    private Reader $moduleReader;

    /**
     * @param Reader $moduleReader
     */
    public function __construct(Reader $moduleReader)
    {
        $this->moduleReader = $moduleReader;
    }

    /**
     * @inheritdoc
     */
    public function getSchema()
    {
        return $this->getXsdPath();
    }

    /**
     * @inheritdoc
     */
    public function getPerFileSchema()
    {
        return $this->getXsdPath();
    }

    private function getXsdPath(): string
    {
        return $this->moduleReader->getModuleDir(
                Dir::MODULE_ETC_DIR,
                'AdPage_GTM')
            . '/' . 'data_layer.xsd';
    }
}
