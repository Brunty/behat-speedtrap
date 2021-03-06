<?php
declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

final class FeatureContext implements Context
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Process
     */
    private $process;

    /**
     * @var string|null
     */
    private $workingDirectory;

    /**
     * @var string|null
     */
    private $input;

    /**
     * @var string|null
     */
    private $option;

    const OUTPUT_TIMEOUT = 5;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * @BeforeScenario
     * @return void
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function createWorkingDirectory()
    {
        $this->workingDirectory = tempnam(sys_get_temp_dir(), 'behat-speedtrap');
        $this->filesystem->remove($this->workingDirectory);
        $this->filesystem->mkdir($this->workingDirectory . '/features/bootstrap', 0777);
    }

    /**
     * @AfterScenario
     * @return void
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function clearWorkingDirectory()
    {
        $this->filesystem->remove($this->workingDirectory);
    }

    /**
     * @BeforeScenario
     * @return void
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     */
    public function createProcess()
    {
        $this->process = new Process(null);
    }

    /**
     * @AfterScenario
     * @return void
     */
    public function stopProcessIfRunning()
    {
        if ($this->process->isRunning()) {
            $this->process->stop();
        }
    }

    /**
     * @Given I have the configuration:
     * @param PyStringNode $config
     * @return void
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function iHaveTheConfiguration(PyStringNode $config)
    {
        $this->filesystem->dumpFile(
            $this->workingDirectory . '/behat.yml',
            $config->getRaw()
        );
    }

    /**
     * @Given I have the feature:
     * @param PyStringNode $content
     * @return void
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function iHaveTheFeature(PyStringNode $content)
    {
        $this->filesystem->dumpFile(
            $this->workingDirectory . '/features/feature.feature',
            $content->getRaw()
        );
    }

    /**
     * @Given I have the context:
     * @param PyStringNode $definition
     * @return void
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function iHaveTheContext(PyStringNode $definition)
    {
        $this->filesystem->dumpFile(
            $this->workingDirectory . '/features/bootstrap/FeatureContext.php',
            $definition->getRaw()
        );
    }

    /**
     * @When I run behat
     * @return void
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     */
    public function iRunBehatWithTheOption()
    {
        $this->runBehat();
    }

    /**
     * @Then I should not see:
     * @param PyStringNode $expected
     * @return void
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws RuntimeException
     */
    public function iShouldNotSee(PyStringNode $expected)
    {
        try {
            $this->iShouldSee($expected);
        } catch (RuntimeException $e) {
            return; // we want to catch this exception as we don't want to see the content
        }

        throw new RuntimeException("Found content {$expected->getRaw()} when it shouldn't have shown");
    }

    /**
     * @Then I should see:
     * @param PyStringNode $expected
     * @return void
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \RuntimeException
     */
    public function iShouldSee(PyStringNode $expected)
    {
        $start = microtime(true);
        while ( ! $this->outputContains($expected->getRaw())) {
            if ( ! $this->process->isRunning() || microtime(true) - $start > self::OUTPUT_TIMEOUT) {
                throw new RuntimeException(
                    sprintf(
                        'Did not get output after expected time. Actual: "%s"',
                        $this->process->getOutput()
                    )
                );
            }
            usleep(50);
        }
    }

    /**
     * @param string $expected
     * @return bool
     * @throws \Symfony\Component\Process\Exception\LogicException
     */
    private function outputContains(string $expected): bool
    {
        $output = $this->normaliseOutput($this->process->getOutput());
        $expected = $this->normaliseOutput($expected);

        if ( ! $output) {
            return false;
        }

        return strpos($output, $expected) !== false;
    }

    /**
     * @return void
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     */
    private function runBehat()
    {
        $phpFinder = new PhpExecutableFinder();
        $phpBin = $phpFinder->find();

        $this->process->setWorkingDirectory($this->workingDirectory);
        $this->process->setCommandLine(
            sprintf(
                '%s %s %s --no-colors',
                $phpBin,
                escapeshellarg(BEHAT_BIN_PATH),
                $this->option
            ) . ' 2>&1'
        );
        $this->process->setInput($this->input);
        $this->process->setPty(true);

        $this->process->start();
    }

    /**
     * Normalise output to remove whitespace, ANSI colour escape codes, and replaces the actual time to run with the
     * string TIME to run for substitutions in scenarios.
     *
     * @example
     *  - 2005ms to run features/feature.feature:2 - This scenario should be logged
     *    becomes
     *  - TIME to run features/feature.feature:2 - This scenario should be logged
     *
     * @param string
     * @return string
     */
    private function normaliseOutput(string $output): string
    {
        return trim(
            preg_replace(
                '/\d+ms to run/',
                'TIME to run',
                preg_replace(
                    '#\\x1b[[][^A-Za-z]*[A-Za-z]#',
                    '',
                    preg_replace('/\\s+/', ' ', $output)
                )
            )
        );
    }
}
