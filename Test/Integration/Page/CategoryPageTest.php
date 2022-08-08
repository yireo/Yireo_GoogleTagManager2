<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Page;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Yireo\GoogleTagManager2\Test\Integration\PageTestCase;

/**
 * @magentoAppArea frontend
 */
class CategoryPageTest extends PageTestCase
{
    /**
     * @magentoConfigFixture current_store googletagmanager2/settings/enabled 1
     * @magentoConfigFixture current_store googletagmanager2/settings/method 1
     * @magentoConfigFixture current_store googletagmanager2/settings/id test
     */
    public function testValidDataLayerWithOneCategory()
    {
        $this->assertEnabledFlagIsWorking();

        $category = $this->createOneCategory();

        $this->dispatch('catalog/category/view/id/' . $category->getId());
        $this->assertRequestActionName('view');

        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString($category->getName(), $body);

        $block = $this->layout->getBlock('yireo_googletagmanager2.data-layer');
        $this->assertNotEmpty($block);

        $this->assertDataLayerEquals($category->getName(), 'categoryName');
        $this->assertDataLayerEquals($category->getId(), 'categoryId');
        $this->assertDataLayerEquals(0, 'categorySize');
        $this->assertDataLayerEquals('catalog/category/view', 'pageType');
    }

    /**
     * @return CategoryInterface
     */
    private function getOneCategory(): CategoryInterface
    {
        $categoryList = $this->objectManager->get(CategoryListInterface::class);
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->addFilter('level', 2, 'gteq');
        $searchCriteriaBuilder->setPageSize(1);
        $searchResults = $categoryList->getList($searchCriteriaBuilder->create());
        $categories = $searchResults->getItems();
        $this->assertNotEmpty($categories);
        $this->assertTrue(count($categories) === 1);

        $category = array_shift($categories);
        $this->assertTrue($category->getId() > 0);
        $this->assertNotEmpty($category->getName());
        return $category;
    }

    private function createOneCategory(): CategoryInterface
    {
        $categoryFactory = $this->objectManager->get(CategoryInterfaceFactory::class);

        /** @var $category \Magento\Catalog\Model\Category */
        $category = $categoryFactory->create();
        $category->isObjectNew(true);
        $category->setId(3)
            ->setName('Category 1')
            ->setParentId(2)
            ->setPath('1/2/3')
            ->setUrlKey('category1')
            ->setLevel(2)
            ->setAvailableSortBy('name')
            ->setDefaultSortBy('name')
            ->setIsActive(true)
            ->setPosition(1)
            ->save();

        return $this->getOneCategory();
    }
}
