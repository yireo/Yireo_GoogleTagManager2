<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Test\Integration\Stub;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CategoryLoadAfterSpy implements ObserverInterface
{
    /**
     * @var int[]
     */
    private array $categoryIds = [];

    public function execute(Observer $observer)
    {
        $this->categoryIds[] = (int)$observer->getEvent()->getData('category')->getId();
    }

    /**
     * @return int[]
     */
    public function getCategoryIds(): array
    {
        return $this->categoryIds;
    }
}
