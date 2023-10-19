<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Event;

use AdPage\GTM\Api\Data\EventInterface;

class Logout implements EventInterface
{
    public function get(): array
    {
        return [
            'event' => 'trytagging_logout'
        ];
    }
}
