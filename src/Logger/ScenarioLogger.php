<?php
declare(strict_types=1);

namespace Brunty\Behat\SpeedtrapExtension\Logger;

class ScenarioLogger implements \Countable
{
    /**
     * @var float[]
     */
    private $scenarioTimes = [];

    /**
     * @param string $scenarioName
     * @return void
     */
    public function logScenarioStarted(string $scenarioName)
    {
        $this->scenarioTimes[$scenarioName] = microtime(true);
    }

    /**
     * @param string $scenarioName
     * @return void
     */
    public function logScenarioFinished(string $scenarioName)
    {
        $this->scenarioTimes[$scenarioName] = microtime(true) - $this->scenarioTimes[$scenarioName];
    }

    /**
     * @param int $threshold
     * @return float[]
     */
    public function getScenariosAboveThreshold(int $threshold): array
    {
        return array_filter(
            $this->scenarioTimes,
            function ($time) use ($threshold) {
                return $time * 1000 > $threshold;
            }
        );
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->scenarioTimes = [];
    }

    public function count(): int
    {
        return \count($this->scenarioTimes);
    }
}
