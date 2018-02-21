<?php

namespace Brunty\Behat\SpeedtrapExtension\Logger;

class ScenarioLogger implements \Countable
{
    private $scenarioTimes = [];

    public function logScenarioStarted(string $scenarioName)
    {
        $this->scenarioTimes[$scenarioName] = microtime(true);
    }

    public function logScenarioFinished(string $scenarioName)
    {
        $this->scenarioTimes[$scenarioName] = microtime(true) - $this->scenarioTimes[$scenarioName];
    }

    public function getScenariosAboveThreshold(int $threshold): array
    {
        return array_filter(
            $this->scenarioTimes,
            function ($time) use ($threshold) {
                return $time * 1000 > $threshold;
            }
        );
    }

    public function clear()
    {
        $this->scenarioTimes = [];
    }

    public function count()
    {
        return count($this->scenarioTimes);
    }
}
