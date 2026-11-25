<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Logger;

use Magento\Framework\Filesystem\DirectoryList;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Yireo\GoogleTagManager2\Config\Config;

class Debugger
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
     * Debugger constructor.
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
     * @param string $msg
     * @param mixed $data
     *
     * @return bool
     */
    public function debug(string $msg, $data = null): bool
    {
        if ($this->config->isDebug() === false) {
            return false;
        }

        if (!empty($data)) {
            $msg .= ': ' . var_export($this->piiRedactor->redact($data), true);
        }

        $this->logger->notice($msg);
        return true;
    }
}
