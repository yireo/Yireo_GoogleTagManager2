<?php declare(strict_types=1);

namespace Tagging\GTM\DataLayer\Event;

use Tagging\GTM\Api\Data\EventInterface;

class SignUp implements EventInterface
{
    public function get(): array
    {
        return [
            'event' => 'trytagging_sign_up',
            'method' => 'Standard' // @TODO: implement mapping based on the route used?
        ];
    }
}
