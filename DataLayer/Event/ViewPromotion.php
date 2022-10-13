<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Yireo\GoogleTagManager2\Api\Data\EventInterface;
use Yireo\GoogleTagManager2\DataLayer\Event\Promotion\PromotionItem;

/**
 * @todo Actually use this
 */
class ViewPromotion implements EventInterface
{
    /**
     * @param PromotionItem $promotionItems
     * @return array
     */
    public function get(array $promotionItems): array
    {
        $promotionsItemsData = [];
        foreach ($promotionItems as $promotionItem) {
            $promotionsItemsData[] = $promotionItem->get();
        }

        return [
            'event' => 'view_promotion',
            'ecommerce' => [
                'items' => $promotionsItemsData
            ]
        ];
    }
}
