<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Util;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class GetCurrentCategory
{
    private RequestInterface $request;
    private CategoryRepositoryInterface $categoryRepository;
    private StoreManagerInterface $storeManager;
    private Registry $registry;

    public function __construct(
        RequestInterface $request,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        Registry $registry
    ) {
        $this->request = $request;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
    }

    /**
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function get(): CategoryInterface
    {
        $category = $this->registry->registry('current_category');
        if ($category instanceof CategoryInterface && $category->getId()) {
            return $category;
        }

        $categoryId = (int)$this->request->getParam('id');
        if ($categoryId < 1) {
            throw new NoSuchEntityException(__('No current category found'));
        }

        /** @var Store $store */
        $store = $this->storeManager->getStore();
        $category = $this->categoryRepository->get($categoryId, $store->getId());
        if (false === $this->isAvailable($category, (int)$store->getRootCategoryId())) {
            throw new NoSuchEntityException(__('No current category found'));
        }

        return $category;
    }

    private function isAvailable(CategoryInterface $category, int $rootCategoryId): bool
    {
        if (false === (bool)$category->getIsActive()) {
            return false;
        }

        $pathIds = array_map('intval', explode('/', (string)$category->getPath()));
        return in_array($rootCategoryId, $pathIds, true);
    }
}
