<?php declare(strict_types=1);

/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author    Jisse Reitsma <jisse@yireo.com>
 * @copyright 2017 Yireo (https://www.yireo.com/)
 * @license   Open Source License (OSL v3)
 */

namespace Tagging\GTM\Util;

use Psr\Log\LoggerInterface;
use Tagging\GTM\Config\Config;

class Debug
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Data constructor.
     *
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Debugging method
     *
     * @param string $string String to debug
     * @param mixed|null $variable Tag to dump to debug message
     *
     * @return bool
     */
    public function debug(string $string, $variable = null)
    {
        if ($this->config->isDebug() === false) {
            return false;
        }

        if ($variable !== null) {
            $string .= ': ' . var_export($variable, true);
        }

        $this->logger->info('Tagging_GTM: ' . $string);

        return true;
    }
}
