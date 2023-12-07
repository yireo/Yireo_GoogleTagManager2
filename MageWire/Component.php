<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\MageWire;

if (class_exists('\Magewirephp\Magewire\Component')) {
    class Component extends \Magewirephp\Magewire\Component {}
} else {
    class Component {}
}
