<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Yireo\GoogleTagManager2\Api\Data\EventInterface;

class Logout implements EventInterface
{
    public function get(): array
    {
        return [
            'event' => 'logout'
        ];
    }
}
