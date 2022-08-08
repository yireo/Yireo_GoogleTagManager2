<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\DataLayer\Tag;

use Magento\Framework\App\State;

/**
 * @see https://developers.google.com/tag-platform/tag-manager/api/v1/reference/accounts/containers/tags
 */
class LiveOnly implements AddTagInterface
{
    private State $state;

    /**
     * @param State $state
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }

    public function addData(): bool
    {
        // @todo: Make this configurable
        return (bool)$this->state->getMode() === State::MODE_PRODUCTION;
    }
}
