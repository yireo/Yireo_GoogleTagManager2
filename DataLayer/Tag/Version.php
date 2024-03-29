<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag;

use Magento\Framework\Component\ComponentRegistrar;
use Yireo\GoogleTagManager2\Api\Data\TagInterface;

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
        $path = $this->composerRegistrar->getPath('module', 'Yireo_GoogleTagManager2');
        $composerPath = $path.'/composer.json';
        $composerData = json_decode(file_get_contents($composerPath), true);
        return $composerData['version'];
    }
}
