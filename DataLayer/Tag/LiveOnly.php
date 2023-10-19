<?php declare(strict_types=1);

namespace AdPage\GTM\DataLayer\Tag;

use Magento\Framework\App\State;
use AdPage\GTM\Api\Data\TagInterface;

class LiveOnly implements TagInterface
{
    private State $state;

    /**
     * @param State $state
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }

    public function get(): bool
    {
        return $this->state->getMode() === State::MODE_PRODUCTION;
    }
}
