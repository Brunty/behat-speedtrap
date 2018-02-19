<?php

namespace Brunty\Behat\SpeedtrapExtension\Printer;

interface OutputPrinter
{
    const SERVICE_ID_PREFIX = 'brunty.speedtrap_extension.output_printer';

    /**
     * @param array $scenarios
     *
     * @return void
     */
    public function printLogs(array $scenarios);
}
