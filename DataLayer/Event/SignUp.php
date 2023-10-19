<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Event;

use AdPage\GTM\Api\Data\EventInterface;

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
