<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Behat context class.
 */
class FeatureContext implements Context
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
     * @var string
     */
    private $workingDirectory;

    /**
     * @var string
     */
    private $input;

    /**
     * @var string
     */
    private $option;

    const OUTPUT_TIMEOUT = 5;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * @BeforeScenario
     */
    public function createWorkingDirectory()
    {
        $this->workingDirectory = tempnam(sys_get_temp_dir(), 'behat-speedtrap');
        $this->filesystem->remove($this->workingDirectory);
        $this->filesystem->mkdir($this->workingDirectory . '/features/bootstrap', 0777);
    }

    /**
     * @AfterScenario
     */
    public function clearWorkingDirectory()
    {
        $this->filesystem->remove($this->workingDirectory);
    }

    /**
     * @BeforeScenario
     */
    public function createProcess()
    {
        $this->process = new Process(null);
    }

    /**
     * @AfterScenario
     */
    public function stopProcessIfRunning()
    {
        if ($this->process->isRunning()) {
            $this->process->stop();
        }
    }

    /**
     * @Given I have the configuration:
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
     */
    public function iRunBehatWithTheOption()
    {
        $this->runBehat();
    }

    /**
     * @Then I should not see:
     */
    public function iShouldNotSee(PyStringNode $expected)
    {
        try {
            $this->iShouldSee($expected);
        } catch (RuntimeException $e) {
            return; // we want to catch this exception as we don't want to see the content
        }

        throw new Exception("Found content {$expected->getRaw()} when it shouldn't have shown");
    }

    /**
     * @Then I should see:
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

    private function outputContains(string $expected)
    {
        $output = $this->normaliseOutput($this->process->getOutput());
        $expected = $this->normaliseOutput($expected);

        if ( ! $output) {
            return false;
        }

        return strstr($output, $expected);
    }

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
     * @param string
     *
     * @return string
     */
    private function normaliseOutput(string $output): string
    {
        return trim(preg_replace('/[0-9ms.]+ \([0-9.GkMb]+\)/', 'TIME', preg_replace('/\\s+/', ' ', $output)));
    }
}
