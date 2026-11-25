<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Util;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Yireo\GoogleTagManager2\Test\Integration\FixtureTrait\GetCategory;
use Yireo\GoogleTagManager2\Util\GetCurrentCategory;

/**
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 * @magentoDataFixture Yireo_GoogleTagManager2::Test/Integration/_files/gtm_category_tree.php
 */
class GetCurrentCategoryTest extends TestCase
{
    use GetCategory;

    protected function tearDown(): void
    {
        Bootstrap::getObjectManager()->get(Registry::class)->unregister('current_category');
    }

    public function testActiveCategoryInStoreRootIsResolvedFromRequest()
    {
        $category = $this->getCategoryByName('GTM Child');
        $this->assertSame((int)$category->getId(), (int)$this->getCurrentCategory($category->getId())->getId());
    }

    public function testCategoryFromRegistryIsPreferred()
    {
        $category = $this->getCategoryByName('GTM Child');
        $otherCategory = $this->getCategoryByName('GTM Parent');
        Bootstrap::getObjectManager()->get(Registry::class)->register('current_category', $category);

        $this->assertSame((int)$category->getId(), (int)$this->getCurrentCategory($otherCategory->getId())->getId());
    }

    public function testInactiveCategoryIsNotResolvedFromRequest()
    {
        $category = $this->getCategoryByName('GTM Inactive');
        $this->expectException(NoSuchEntityException::class);
        $this->getCurrentCategory($category->getId());
    }

    public function testCategoryOutsideStoreRootIsNotResolvedFromRequest()
    {
        $category = $this->getCategoryByName('GTM Outside Root');
        $this->expectException(NoSuchEntityException::class);
        $this->getCurrentCategory($category->getId());
    }

    public function testUnknownCategoryIsNotResolvedFromRequest()
    {
        $this->expectException(NoSuchEntityException::class);
        $this->getCurrentCategory(999999);
    }

    private function getCurrentCategory($categoryId): CategoryInterface
    {
        $objectManager = Bootstrap::getObjectManager();
        $request = $objectManager->get(RequestInterface::class);
        $request->setParams(['id' => (string)$categoryId]);

        return $objectManager->create(GetCurrentCategory::class)->get();
    }
}
