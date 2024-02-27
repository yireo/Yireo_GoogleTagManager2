<?php declare(strict_types=1);

// phpcs:ignoreFile -- Only 1 class allowed in one file

namespace Yireo\GoogleTagManager2\MageWire;

if (class_exists('\Magewirephp\Magewire\Component')) {
    class Component extends \Magewirephp\Magewire\Component
    {
        public function isHyvaCheckoutEnabled(): bool
        {
            return true;
        }
    }
} else {
    class Component
    {
        public function isHyvaCheckoutEnabled(): bool
        {
            return false;
        }
    }
}
