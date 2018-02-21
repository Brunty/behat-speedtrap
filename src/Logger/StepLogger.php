<?php

namespace Brunty\Behat\SpeedtrapExtension\Logger;

class StepLogger implements \Countable
{
    private $stepTimes = [];

    public function logStepStarted(string $stepName)
    {
        $this->stepTimes[$stepName] = microtime(true);
    }

    public function logStepFinished(string $stepName)
    {
        $this->stepTimes[$stepName] = microtime(true) - $this->stepTimes[$stepName];
    }

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

    public function clear()
    {
        $this->stepTimes = [];
    }

    public function count(): int
    {
        return \count($this->stepTimes);
    }
}
