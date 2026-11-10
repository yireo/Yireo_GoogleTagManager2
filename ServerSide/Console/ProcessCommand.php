<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\ServerSide\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yireo\GoogleTagManager2\ServerSide\Processor;

class ProcessCommand extends Command
{
    private Processor $processor;

    public function __construct(
        Processor $processor,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->processor = $processor;
    }

    protected function configure()
    {
        $this->setName('yireo_googletagmanager2:serverside:process');
        $this->setDescription('Send pending and failed server-side tracking events');
        $this->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Maximum number of events per status', '100');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = max(1, (int)$input->getOption('limit'));
        $sent = $this->processor->processPending($limit);
        $retried = $this->processor->retryFailed($limit);
        $output->writeln('Sent ' . $sent . ' pending events and ' . $retried . ' retried events');

        return Command::SUCCESS;
    }
}
