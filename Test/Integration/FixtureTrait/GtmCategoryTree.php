<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\FixtureTrait;

trait GtmCategoryTree
{
    /**
     * Active categories of product "gtm-multi-category" within the root category of the default store view
     *
     * @return string[]
     */
    public function getGtmActiveCategoryNames(): array
    {
        return [
            'GTM Parent',
            'GTM Child',
            'GTM Grandchild',
            'GTM Extra 1',
            'GTM Extra 2',
            'GTM Extra 3',
            'GTM Store Disabled',
        ];
    }

    /**
     * @param array $itemData
     * @return string[]
     */
    public function getItemCategories(array $itemData): array
    {
        $itemCategories = [];
        foreach ($itemData as $key => $value) {
            if (preg_match('/^item_category(\d*)$/', (string)$key)) {
                $itemCategories[$key] = $value;
            }
        }

        return $itemCategories;
    }

    public function assertValidGtmItemCategories(array $itemData): void
    {
        $itemCategories = $this->getItemCategories($itemData);
        $message = json_encode($itemData);

        $this->assertSame(
            ['item_category', 'item_category2', 'item_category3', 'item_category4', 'item_category5'],
            array_keys($itemCategories),
            $message
        );
        $this->assertCount(5, array_unique($itemCategories), $message);
        foreach ($itemCategories as $itemCategory) {
            $this->assertContains($itemCategory, $this->getGtmActiveCategoryNames(), $message);
        }
    }
}
