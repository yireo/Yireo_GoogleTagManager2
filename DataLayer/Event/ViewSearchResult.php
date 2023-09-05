<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Yireo\GoogleTagManager2\Api\Data\EventInterface;

class ViewSearchResult implements EventInterface
{
    private ?string $searchTerm = null;

    public function get(): array
    {
        return [
            'event' => 'view_search_result',
            'search_term' => $this->searchTerm,
        ];
    }

    public function setSearchTerm(?string $searchTerm): void
    {
        $this->searchTerm = $searchTerm;
    }
}
