<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Tag;

use Magento\Framework\Component\ComponentRegistrar;
use AdPage\GTM\Api\Data\TagInterface;

class Version implements TagInterface
{
    private ComponentRegistrar $composerRegistrar;

    /**
     * @param ComponentRegistrar $composerRegistrar
     */
    public function __construct(
        ComponentRegistrar $composerRegistrar
    ) {
        $this->composerRegistrar = $composerRegistrar;
    }

    public function get(): string
    {
        $path = $this->composerRegistrar->getPath('module', 'AdPage_GTM');
        $composerPath = $path.'/composer.json';
        $composerData = json_decode(file_get_contents($composerPath), true);
        return $composerData['version'];
    }
}
