<?php

namespace PhpBrew\Command;

use PhpBrew\Console;
use PhpBrew\Console\ArgumentCollection;
use PhpBrew\Console\Formatter;
use PhpBrew\Console\Logger;
use PhpBrew\Console\OptionCollection;
use PhpBrew\Console\OptionResult;

abstract class BaseCommand
{
    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var OptionResult
     */
    public $options;

    /**
     * @var Formatter
     */
    public $formatter;

    /**
     * @var Console|null
     */
    protected $application;

    /**
     * @var array<string, string>
     */
    protected $subcommands = array();

    /**
     * @var string
     */
    protected $name = '';

    public function __construct($name = '')
    {
        $this->name = $name;
        $this->logger = new Logger();
        $this->options = new OptionResult();
        $this->formatter = new Formatter();
    }

    public function setApplication(Console $application)
    {
        $this->application = $application;
        $this->logger = $application->getLogger();
        $this->formatter = $application->getFormatter();
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function brief()
    {
        return '';
    }

    public function usage()
    {
        return '';
    }

    public function aliases()
    {
        return array();
    }

    public function init()
    {
    }

    public function prepare()
    {
        return true;
    }

    /**
     * @param OptionCollection $opts
     */
    public function options($opts)
    {
    }

    /**
     * @param ArgumentCollection $args
     */
    public function arguments($args)
    {
    }

    /**
     * Register a subcommand on this command.
     *
     * @param string      $name
     * @param string|null $className
     */
    public function command($name, $className = null)
    {
        if ($className === null) {
            $parts = array_map('ucfirst', explode('-', $name));
            $shortClass = implode('', $parts) . 'Command';
            $selfClass = get_class($this);
            $className = $selfClass . '\\' . $shortClass;
        }
        $this->subcommands[$name] = $className;
    }

    /**
     * @return array<string, string>
     */
    public function getSubcommands()
    {
        return $this->subcommands;
    }
}
