<?php
namespace Brunty\Behat\SpeedtrapExtension\Printer;

use Brunty\Behat\SpeedtrapExtension\ServiceContainer\Config;
use Symfony\Component\Console\Output\ConsoleOutput;

class ConsoleScenarioOutputPrinter implements OutputPrinter
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
     * @param  array $scenarios
     *
     * @return void
     */
    public function printLogs(array $scenarios)
    {
        $output = new ConsoleOutput();

        $output->writeln('');

        if (empty($scenarios)) {
            return;
        }

        $output->writeln("The following scenarios were above your configured threshold: {$this->config->getThreshold()}ms");

        $numberOutputted = 0;
        foreach ($scenarios as $scenario => $time) {
            if ($numberOutputted >= $this->config->getReportLength()) {
                continue;
            }
            $numberOutputted++;
            $time = round($time * 1000);
            $output->writeln("<comment>{$time}ms</comment> to run {$scenario}");
        }

        $totalNumberOfSlowScenarios = count($scenarios);
        if ($numberOutputted !== $totalNumberOfSlowScenarios) {
            $output->writeln("<comment>Of a total of {$totalNumberOfSlowScenarios} scenarios logged</comment>");
        }

        $output->writeln('');
    }
}
