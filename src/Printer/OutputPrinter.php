<?php
declare(strict_types=1);

namespace Brunty\Behat\SpeedtrapExtension\Printer;

interface OutputPrinter
{
    /**
     * @param float[] $scenarios
     * @return void
     */
    public function printLogs(array $scenarios);
}
