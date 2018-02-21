<?php
declare(strict_types=1);

namespace Brunty\Behat\SpeedtrapExtension\Logger;

class StepLogger implements \Countable
{
    /**
     * @var float[]
     */
    private $stepTimes = [];

    /**
     * @param string $stepName
     * @return void
     */
    public function logStepStarted(string $stepName)
    {
        $this->stepTimes[$stepName] = microtime(true);
    }

    /**
     * @param string $stepName
     * @return void
     */
    public function logStepFinished(string $stepName)
    {
        $this->stepTimes[$stepName] = microtime(true) - $this->stepTimes[$stepName];
    }

    /**
     * @param int $threshold
     * @return float[]
     */
    public function getStepsAboveThreshold(int $threshold): array
    {
        if ($threshold === 0) {
            return [];
        }
        return array_filter(
            $this->stepTimes,
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
        $this->stepTimes = [];
    }

    public function count(): int
    {
        return \count($this->stepTimes);
    }
}
