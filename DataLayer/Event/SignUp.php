<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Event;

use Yireo\GoogleTagManager2\Api\Data\EventInterface;

class SignUp implements EventInterface
{
    public function get(): array
    {
        return [
            'event' => 'sign_up',
            'method' => 'Standard' // @TODO: implement mapping based on the route used?
        ];
    }
}
