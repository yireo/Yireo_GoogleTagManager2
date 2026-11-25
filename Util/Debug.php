<?php declare(strict_types=1);

/**
 * GoogleTagManager2 plugin for Magento
 *
 * @author    Jisse Reitsma <jisse@yireo.com>
 * @copyright 2017 Yireo (https://www.yireo.com/)
 * @license   Open Source License (OSL v3)
 */

namespace Yireo\GoogleTagManager2\Util;

use Psr\Log\LoggerInterface;
use Yireo\GoogleTagManager2\Config\Config;
use Yireo\GoogleTagManager2\Logger\PiiRedactor;

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

    private PiiRedactor $piiRedactor;

    /**
     * Data constructor.
     *
     * @param Config $config
     * @param LoggerInterface $logger
     * @param PiiRedactor $piiRedactor
     */
    public function __construct(
        Config $config,
        LoggerInterface $logger,
        PiiRedactor $piiRedactor
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->piiRedactor = $piiRedactor;
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
            $string .= ': ' . var_export($this->piiRedactor->redact($variable), true);
        }

        $this->logger->info('Yireo_GoogleTagManager: ' . $string);

        return true;
    }
}
