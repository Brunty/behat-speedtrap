<?php

namespace Brunty\Behat\SpeedtrapExtension\Listener;

use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\BeforeScenarioTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Brunty\Behat\SpeedtrapExtension\ServiceContainer\Config;
use Brunty\Behat\SpeedtrapExtension\Logger\SpeedtrapLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SpeedtrapListener implements EventSubscriberInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var SpeedtrapLogger
     */
    private $speedtrapLogger;

    /**
     * @param Config          $config
     * @param SpeedtrapLogger $speedtrapLogger
     */
    public function __construct(Config $config, SpeedtrapLogger $speedtrapLogger)
    {
        $this->config = $config;
        $this->speedtrapLogger = $speedtrapLogger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ScenarioTested::BEFORE => 'scenarioStarted',
            ScenarioTested::AFTER => 'scenarioFinished',
            SuiteTested::AFTER => 'suiteFinished'
        ];
    }

    /**
     * @param BeforeScenarioTested $event
     */
    public function scenarioStarted(BeforeScenarioTested $event)
    {
        $this->speedtrapLogger->logScenarioStarted($this->getFormattedScenarioName($event));
    }

    /**
     * @param AfterScenarioTested $event
     */
    public function scenarioFinished(AfterScenarioTested $event)
    {
        $this->speedtrapLogger->logScenarioFinished($this->getFormattedScenarioName($event));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function suiteFinished()
    {
        $avgTimes = $this->speedtrapLogger->getScenariosAboveThreshold($this->config->getThreshold());
        $this->speedtrapLogger->clear();

        foreach ($this->config->getOutputPrinters() as $printer) {
            $printer->printLogs($avgTimes);
        }
    }

    /**
     * @param ScenarioTested $event
     *
     * @return null|string
     */
    private function getFormattedScenarioName(ScenarioTested $event)
    {
        return "{$event->getFeature()->getFile()}:{$event->getNode()->getLine()} - {$event->getScenario()->getTitle()}";
    }
}
