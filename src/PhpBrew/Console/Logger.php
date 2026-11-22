<?php

namespace PhpBrew\Console;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class Logger
{
    const QUIET = 0;
    const ERROR = 1;
    const WARN = 2;
    const NOTICE = 3;
    const INFO = 4;
    const DEBUG = 5;

    /**
     * @var array<string, string>
     */
    public $levelStyles = array(
        'error' => 'red',
        'warn' => 'yellow',
        'notice' => 'green',
        'info' => 'white',
        'debug' => 'cyan',
    );

    /**
     * @var int
     */
    protected $level = self::INFO;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Logger|null
     */
    protected static $instance;

    public function __construct(?OutputInterface $output = null)
    {
        $this->output = $output ?: new ConsoleOutput();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function setQuiet()
    {
        $this->level = self::QUIET;
    }

    public function setDebug()
    {
        $this->level = self::DEBUG;
    }

    public function isQuiet()
    {
        return $this->level === self::QUIET;
    }

    public function isDebug()
    {
        return $this->level >= self::DEBUG;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function debug($msg)
    {
        if ($this->level >= self::DEBUG) {
            $this->output->writeln((string) $msg);
        }
    }

    public function info($msg)
    {
        if ($this->level >= self::INFO) {
            $this->output->writeln((string) $msg);
        }
    }

    public function notice($msg)
    {
        if ($this->level >= self::NOTICE) {
            $this->output->writeln((string) $msg);
        }
    }

    public function warn($msg)
    {
        if ($this->level >= self::WARN) {
            $this->output->writeln('<comment>' . $msg . '</comment>');
        }
    }

    public function warning($msg)
    {
        $this->warn($msg);
    }

    public function error($msg)
    {
        if ($this->level >= self::ERROR) {
            $this->output->writeln('<error>' . $msg . '</error>');
        }
    }

    public function write($msg)
    {
        $this->output->write((string) $msg);
    }

    public function writeln($msg = '')
    {
        $this->output->writeln((string) $msg);
    }

    public function newline($count = 1)
    {
        for ($i = 0; $i < $count; $i++) {
            $this->output->writeln('');
        }
    }
}
