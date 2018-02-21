<?php

namespace Brunty\Behat\SpeedtrapExtension\Listener;

use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\BeforeScenarioTested;
use Behat\Behat\EventDispatcher\Event\BeforeStepTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Brunty\Behat\SpeedtrapExtension\Logger\StepLogger;
use Brunty\Behat\SpeedtrapExtension\ServiceContainer\Config;
use Brunty\Behat\SpeedtrapExtension\Logger\ScenarioLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SpeedtrapListener implements EventSubscriberInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ScenarioLogger
     */
    private $scenarioLogger;

    /**
     * @var StepLogger
     */
    private $stepLogger;

    /**
     * @param Config $config
     * @param ScenarioLogger $scenarioLogger
     * @param StepLogger $stepLogger
     */
    public function __construct(Config $config, ScenarioLogger $scenarioLogger, StepLogger $stepLogger)
    {
        $this->config = $config;
        $this->scenarioLogger = $scenarioLogger;
        $this->stepLogger = $stepLogger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StepTested::BEFORE => 'stepStarted',
            StepTested::AFTER => 'stepFinished',
            ScenarioTested::BEFORE => 'scenarioStarted',
            ScenarioTested::AFTER => 'scenarioFinished',
            SuiteTested::AFTER => 'suiteFinished'
        ];
    }

    /**
     * @param BeforeStepTested $event
     */
    public function stepStarted(BeforeStepTested $event)
    {
        $this->stepLogger->logStepStarted($this->getFormattedStepName($event));
    }

    /**
     * @param AfterStepTested $event
     */
    public function stepFinished(AfterStepTested $event)
    {
        $this->stepLogger->logStepFinished($this->getFormattedStepName($event));
    }

    /**
     * @param BeforeScenarioTested $event
     */
    public function scenarioStarted(BeforeScenarioTested $event)
    {
        $this->scenarioLogger->logScenarioStarted($this->getFormattedScenarioName($event));
    }

    /**
     * @param AfterScenarioTested $event
     */
    public function scenarioFinished(AfterScenarioTested $event)
    {
        $this->scenarioLogger->logScenarioFinished($this->getFormattedScenarioName($event));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function suiteFinished()
    {
        $this->outputScenarios();
        $this->outputSteps();
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function outputScenarios()
    {
        $avgTimes = $this->scenarioLogger->getScenariosAboveThreshold($this->config->getScenarioThreshold());
        $this->scenarioLogger->clear();

        foreach ($this->config->getOutputPrinters() as $printer) {
            $printer->printLogs($avgTimes);
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function outputSteps()
    {
        $avgTimes = $this->stepLogger->getStepsAboveThreshold($this->config->getStepThreshold());
        $this->stepLogger->clear();

        foreach ($this->config->getStepOutputPrinters() as $printer) {
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

    /**
     * @param StepTested $event
     * @return string
     */
    private function getFormattedStepName(StepTested $event): string
    {
        $step = $event->getStep();
        return sprintf(
            '%s:%s - %s %s',
            $event->getFeature()->getFile(),
            $event->getNode()->getLine(),
            $step->getKeyword(),
            $step->getText()
        );
    }
}
