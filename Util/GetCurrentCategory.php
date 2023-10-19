<?php declare(strict_types=1);

namespace AdPage\GTM\Util;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class GetCurrentCategory
{
    private RequestInterface $request;
    private CategoryRepositoryInterface $categoryRepository;
    private StoreManagerInterface $storeManager;

    public function __construct(
        RequestInterface $request,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function get(): CategoryInterface
    {
        $categoryId = (int)$this->request->getParam('id');
        try {
            $category = $this->categoryRepository->get($categoryId);
        } catch (NoSuchEntityException $e) {
            /** @var Store $store */
            $store = $this->storeManager->getStore();
            $category = $this->categoryRepository->get($store->getRootCategoryId());
        }

        return $category;
    }
}
