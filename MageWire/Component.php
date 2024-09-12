<?php declare(strict_types=1);

// phpcs:ignoreFile -- Only 1 class allowed in one file

namespace Yireo\GoogleTagManager2\MageWire;

use Magento\Framework\View\Element\Block\ArgumentInterface;

if (false === class_exists('\Magewirephp\Magewire\Component')) {
    class Component implements ArgumentInterface
    {
        public function isHyvaCheckoutEnabled(): bool
        {
            return false;
        }

        public function dispatchBrowserEvent(string $browserEvent, $data)
        {
        }
    }
} else {
    class Component extends \Magewirephp\Magewire\Component
    {
        public function isHyvaCheckoutEnabled(): bool
        {
            return true;
        }
    }

}
