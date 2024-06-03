<?php declare(strict_types=1);

namespace Tagging\GTM\DataLayer\Event;

use Tagging\GTM\Api\Data\EventInterface;
use Tagging\GTM\DataLayer\Event\Promotion\PromotionItem;

/**
 * @todo Implement this class
 */
class ViewPromotion implements EventInterface
{
    /** @var PromotionItem[] */
    private array $promotionItems = [];

    /**
     * @return array
     */
    public function get(): array
    {
        $promotionsItemsData = [];
        foreach ($this->promotionItems as $promotionItem) {
            $promotionsItemsData[] = $promotionItem->get();
        }

        return [
            'event' => 'trytagging_view_promotion',
            'ecommerce' => [
                'items' => $promotionsItemsData
            ]
        ];
    }

    /**
     * @param PromotionItem[] $promotionItems
     * @return ViewPromotion
     */
    public function setPromotionItems(array $promotionItems): ViewPromotion
    {
        $this->promotionItems = $promotionItems;
        return $this;
    }
}
