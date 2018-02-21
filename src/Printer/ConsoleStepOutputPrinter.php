<?php
declare(strict_types=1);

namespace Brunty\Behat\SpeedtrapExtension\Printer;

use Brunty\Behat\SpeedtrapExtension\ServiceContainer\Config;
use Symfony\Component\Console\Output\ConsoleOutput;

class ConsoleStepOutputPrinter implements OutputPrinter
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function printLogs(array $steps)
    {
        $output = new ConsoleOutput();

        $output->writeln('');

        if (empty($steps)) {
            return;
        }

        $output->writeln("The following steps were above your configured threshold: {$this->config->getStepThreshold()}ms");

        $numberOutputted = 0;
        foreach ($steps as $step => $time) {
            if ($numberOutputted >= $this->config->getReportLength()) {
                continue;
            }
            $numberOutputted++;
            $time = round($time * 1000);
            $output->writeln("<comment>{$time}ms</comment> to run step in {$step}");
        }

        $totalNumberOfSlowSteps = \count($steps);
        if ($numberOutputted !== $totalNumberOfSlowSteps) {
            $output->writeln("<comment>Of a total of {$totalNumberOfSlowSteps} steps logged</comment>");
        }

        $output->writeln('');
    }
}
