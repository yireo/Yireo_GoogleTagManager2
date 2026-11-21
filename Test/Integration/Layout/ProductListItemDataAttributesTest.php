<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Layout;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;

/**
 * The Hyva product tile (Magento_Catalog::product/list/item.phtml) renders a data-* attribute for every
 * entry of the "product_data_attributes" argument of the "product_list_item" block. The Hyva click tracking
 * reads data-entity_id from it, because non-salable tiles have no product form input.
 *
 * @magentoAppArea frontend
 */
class ProductListItemDataAttributesTest extends TestCase
{
    public function testEntityIdIsMergedWithAttributesOfALaterBlockDeclaration()
    {
        $layout = $this->createLayout();
        $layout->getUpdate()->addHandle('catalog_list_item');
        $layout->getUpdate()->load();
        $layout->getUpdate()->addUpdate(
            '<body><block class="Magento\Framework\View\Element\Template" name="product_list_item">'
            . '<arguments><argument name="product_data_attributes" xsi:type="array">'
            . '<item name="sku" xsi:type="boolean">true</item>'
            . '</argument></arguments></block></body>'
        );

        $this->assertProductDataAttributes($layout);
    }

    public function testEntityIdIsAddedToHyvaProductListItem()
    {
        $themeProvider = ObjectManager::getInstance()->get(ThemeProviderInterface::class);
        foreach (['Hyva/default', 'Hyva/default-csp'] as $themePath) {
            $theme = $themeProvider->getThemeByFullPath('frontend/' . $themePath);
            if ($theme->getId()) {
                break;
            }
        }

        if (!$theme->getId()) {
            $this->markTestSkipped('No Hyva default theme is installed');
        }

        ObjectManager::getInstance()->get(DesignInterface::class)->setDesignTheme($theme, 'frontend');

        $layout = $this->createLayout();
        $layout->getUpdate()->addHandle('catalog_list_item');
        $layout->getUpdate()->load();

        $this->assertProductDataAttributes($layout);
    }

    private function createLayout(): LayoutInterface
    {
        return ObjectManager::getInstance()->create(LayoutInterface::class);
    }

    private function assertProductDataAttributes(LayoutInterface $layout): void
    {
        $layout->generateXml();
        $layout->generateElements();

        $block = $layout->getBlock('product_list_item');
        $this->assertNotFalse($block, 'Block "product_list_item" not found');

        $attributes = $block->getData('product_data_attributes');
        $this->assertIsArray($attributes);
        $this->assertTrue($attributes['entity_id'] ?? false, 'entity_id missing: ' . json_encode($attributes));
        $this->assertTrue($attributes['sku'] ?? false, 'sku missing: ' . json_encode($attributes));
    }
}
