<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Console;

use Magento\Framework\Serialize\Serializer\Json;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yireo\GoogleTagManager2\ServerSide\Config;
use Yireo\GoogleTagManager2\ServerSide\Context\ContextCollector;
use Yireo\GoogleTagManager2\ServerSide\Payload\MeasurementProtocolMapper;
use Yireo\GoogleTagManager2\ServerSide\Sender;

class TestCommand extends Command
{
    private Config $config;
    private Sender $sender;
    private MeasurementProtocolMapper $mapper;
    private ContextCollector $contextCollector;
    private Json $json;

    public function __construct(
        Config $config,
        Sender $sender,
        MeasurementProtocolMapper $mapper,
        ContextCollector $contextCollector,
        Json $json,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->config = $config;
        $this->sender = $sender;
        $this->mapper = $mapper;
        $this->contextCollector = $contextCollector;
        $this->json = $json;
    }

    protected function configure()
    {
        $this->setName('yireo_googletagmanager2:serverside:test');
        $this->setDescription('Send a test event to the configured server-side tracking endpoint');
        $this->addOption('store', 's', InputOption::VALUE_REQUIRED, 'Store ID', '1');
        $this->addOption('live', null, InputOption::VALUE_NONE, 'Send to the live endpoint instead of the validation endpoint');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $storeId = (int)$input->getOption('store');
        if (false === $this->config->isEnabled($storeId)) {
            $output->writeln('<error>Server-side tracking is disabled or not fully configured for store ' . $storeId . '</error>');
            return Command::FAILURE;
        }

        $validation = false === (bool)$input->getOption('live');
        $body = $this->mapper->map(
            ['event' => 'yireo_gtm2_test', 'source' => 'cli'],
            $this->contextCollector->createFallback()
        );

        $output->writeln('Endpoint: ' . $this->config->getEndpointUrl($storeId, $validation));
        $result = $this->sender->send($storeId, $this->json->serialize($body), $validation);
        $output->writeln('HTTP status: ' . $result->getStatus());
        if ($result->getBody() !== '') {
            $output->writeln('Response: ' . $result->getBody());
        }

        if (false === $result->isSuccess()) {
            $output->writeln('<error>Test event failed</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>Test event sent successfully</info>');
        return Command::SUCCESS;
    }
}
