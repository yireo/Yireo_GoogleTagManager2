<?php

declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Util;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class GetCurrentCategory
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * GetCurrentCategory constructor.
     * @param CategoryRepositoryInterface $categoryRepository
     * @param RequestInterface $request
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        RequestInterface $request
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->request = $request;
    }

    /**
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function get(): CategoryInterface
    {
        $categoryId = (int) $this->request->getParam('id');
        return $this->categoryRepository->get($categoryId);
    }
}
