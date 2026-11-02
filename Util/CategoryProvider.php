<?php
declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Util;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Exception\NotUsingSetProductSkusException;

class CategoryProvider
{
    /**
     * @var int[]
     */
    private array $categoryIds = [];

    /**
     * @var CategoryInterface[]
     */
    private array $loadedCategories = [];

    private CategoryCollectionFactory $categoryCollectionFactory;
    private StoreManagerInterface $storeManager;
    private Config $config;
    private EventManagerInterface $eventManager;

    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        StoreManagerInterface $storeManager,
        Config $config,
        EventManagerInterface $eventManager
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->eventManager = $eventManager;
    }

    /**
     * @param int[] $categoryIds
     * @return void
     * @throws NoSuchEntityException
     */
    public function addCategoryIds(array $categoryIds)
    {
        $categoryIds = $this->filterRootCategoryIdFromCategoryIds($categoryIds);
        if (empty($categoryIds)) {
            return;
        }

        $this->categoryIds = array_unique(array_merge($this->categoryIds, $categoryIds));
    }

    /**
     * @param int $categoryId
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $categoryId): CategoryInterface
    {
        foreach ($this->getLoadedCategories() as $category) {
            if ((int)$category->getId() === $categoryId) {
                return $category;
            }
        }

        throw new NotUsingSetProductSkusException('Using getCategoryById() delivers no result');
    }

    /**
     * @return CategoryInterface[]
     * @throws NoSuchEntityException
     */
    public function getLoadedCategories(): array
    {
        if (empty($this->categoryIds)) {
            throw new NotUsingSetProductSkusException('Using getCategories() before setCategoryIds()');
        }

        $loadCategoryIds = array_diff($this->categoryIds, array_keys($this->loadedCategories));
        if (count($loadCategoryIds) > 0) {
            foreach ($this->loadCategoriesByIds($loadCategoryIds) as $category) {
                $this->loadedCategories[(int)$category->getId()] = $category;
            }
        }

        return array_filter($this->loadedCategories, static function (CategoryInterface $category) {
            return $category->getIsActive();
        });
    }

    /**
     * @param ProductInterface $product
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function getFirstByProduct(ProductInterface $product): CategoryInterface
    {
        /** @var Product $product */
        $productCategoryIds = $product->getCategoryIds();
        $productCategoryIds = $this->filterRootCategoryIdFromCategoryIds($productCategoryIds);
        if (empty($productCategoryIds)) {
            throw new NoSuchEntityException(__('Product "%1" has no categories', $product->getSku()));
        }

        $category = null;
        while ($category === null && $productCategoryId = array_shift($productCategoryIds)) {
            $this->addCategoryIds([$productCategoryId]);
            if ($this->categoryIds) {
                $category = $this->getLoadedCategories()[$productCategoryId] ?? null;
            }
        }

        if ($category instanceof CategoryInterface) {
            return $category;
        }

        throw new NoSuchEntityException(__('Product "%1" has no categories', $product->getSku()));
    }

    /**
     * @param ProductInterface $product
     * @return CategoryInterface[]
     * @throws NoSuchEntityException
     */
    public function getAllByProduct(ProductInterface $product): array
    {
        /** @var Product $product */
        $productCategoryIds = $product->getCategoryIds();
        $productCategoryIds = $this->filterRootCategoryIdFromCategoryIds($productCategoryIds);
        if (empty($productCategoryIds)) {
            throw new NoSuchEntityException(__('Product "%1" has no categories', $product->getSku()));
        }

        $this->addCategoryIds($productCategoryIds);

        return array_intersect_key($this->getLoadedCategories(), array_flip($productCategoryIds));
    }

    /**
     * @param array $categoryIds
     * @return CategoryInterface[]
     * @throws NoSuchEntityException
     */
    private function loadCategoriesByIds(array $categoryIds): array
    {
        $collection = $this->categoryCollectionFactory->create();
        foreach ($this->getAttributeCodesToSelect() as $attributeCode) {
            try {
                $collection->addAttributeToSelect($attributeCode);
            } catch (LocalizedException $exception) {
                // Skip attributes that are configured but do not exist
            }
        }

        $collection
            ->addIdFilter($categoryIds)
            ->addAttributeToFilter('path', ['like' => '1/' . $this->getRootCategoryId() . '/%']);

        /** @var Category[] $categories */
        $categories = $collection->getItems();

        // Keep observers of single category loads working, like with CategoryRepositoryInterface::get()
        foreach ($categories as $category) {
            $this->eventManager->dispatch(
                'catalog_category_load_after',
                ['category' => $category, 'data_object' => $category]
            );
        }

        return $categories;
    }

    /**
     * @return string[]
     */
    private function getAttributeCodesToSelect(): array
    {
        $attributeCodes = array_merge(['name', 'is_active'], $this->config->getCategoryEavAttributeCodes());
        $attributeCodes = array_map('trim', $attributeCodes);
        $attributeCodes = array_filter($attributeCodes, static function (string $attributeCode) {
            return $attributeCode !== '' && $attributeCode !== 'id';
        });

        return array_values(array_unique($attributeCodes));
    }

    /**
     * @param array $categoryIds
     * @return array
     * @throws NoSuchEntityException
     */
    private function filterRootCategoryIdFromCategoryIds(array $categoryIds): array
    {
        $rootCategoryId = $this->getRootCategoryId();
        return array_filter($categoryIds, static function ($categoryId) use ($rootCategoryId) {
            return (int)$categoryId !== $rootCategoryId;
        });
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    private function getRootCategoryId(): int
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();
        return (int)$store->getRootCategoryId();
    }
}
