<?php

namespace PhpBrew\Console;

use Symfony\Component\Console\Input\InputArgument;

class ArgumentCollection
{
    /**
     * @var ArgumentDefinition[]
     */
    protected $arguments = array();

    public function add($name, $description = '', $mode = InputArgument::REQUIRED, $default = null)
    {
        $arg = new ArgumentDefinition($name, $description, $mode, $default);
        $this->arguments[] = $arg;
        return $arg;
    }

    /**
     * @return ArgumentDefinition[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }
}
