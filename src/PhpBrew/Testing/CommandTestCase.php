<?php

namespace PhpBrew\Testing;

use PhpBrew\Console;
use PhpBrew\Console\Option;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class CommandTestCase extends TestCase
{
    protected $debug = false;

    /**
     * @var Console
     */
    protected $app;

    private $previousPhpBrewRoot;

    private $previousPhpBrewHome;

    public $primaryVersion = '7.0.33';

    /**
     * You need to set this to true in each subclass you want to use VCR in.
     */
    public $usesVCR = false;

    public function getPrimaryVersion()
    {
        return $this->primaryVersion;
    }

    public function setupApplication()
    {
        $console = Console::getInstance();
        $console->getLogger()->setQuiet();
        $console->getFormatter()->preferRawOutput();

        return $console;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = $this->setupApplication();
        $this->previousPhpBrewRoot = getenv('PHPBREW_ROOT');
        $this->previousPhpBrewHome = getenv('PHPBREW_HOME');

        if ($options = Console::getInstance()->options) {
            $option = new Option('no-progress');
            $option->setValue(true);
            $options->set('no-progress', $option);
        }

        if ($this->usesVCR) {
            VCRAdapter::enableVCR($this);
        }
    }

    protected function tearDown(): void
    {
        if ($this->usesVCR) {
            VCRAdapter::disableVCR();
        }
        parent::tearDown();
    }

    public function assertCommandSuccess($args)
    {
        try {
            if ($this->debug) {
                fwrite(STDERR, $args . PHP_EOL);
            }

            $output = new BufferedOutput();
            $ret = $this->executeCommand($args, $output);
            $this->assertTrue($ret, $output->fetch());
        } catch (\CurlKit\CurlException $e) {
            $this->markTestIncomplete($e->getMessage());
        }
    }

    public function runCommand($args)
    {
        $output = new BufferedOutput();
        return $this->executeCommand($args, $output);
    }

    public function runCommandWithStdout($args)
    {
        $output = new BufferedOutput();
        $ret = $this->executeCommand($args, $output);
        echo $output->fetch();
        return $ret;
    }

    protected function executeCommand($args, BufferedOutput $output)
    {
        $console = $this->setupApplication();
        $tokens = is_array($args) ? $args : preg_split('/\s+/', trim($args));
        return $console->runWithTry($tokens, $output);
    }
}
