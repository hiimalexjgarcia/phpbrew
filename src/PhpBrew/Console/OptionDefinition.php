<?php

namespace PhpBrew\Console;

use Symfony\Component\Console\Input\InputOption;

class OptionDefinition
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $shortcut;

    /**
     * @var int
     */
    protected $mode;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var mixed
     */
    protected $default;

    /**
     * @var bool
     */
    protected $incremental = false;

    public function __construct($name, $shortcut, $mode, $description = '', $default = null)
    {
        $this->name = $name;
        $this->shortcut = $shortcut;
        $this->mode = $mode;
        $this->description = $description;
        $this->default = $default;
    }

    public function isa($type)
    {
        return $this;
    }

    public function valueName($name)
    {
        return $this;
    }

    public function defaultValue($default)
    {
        $this->default = $default;
        return $this;
    }

    public function incremental()
    {
        $this->incremental = true;
        return $this;
    }

    public function validValues($values)
    {
        return $this;
    }

    public function suggestions($suggestions)
    {
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function toInputOption()
    {
        $default = ($this->mode === InputOption::VALUE_NONE) ? null : $this->default;

        return new InputOption(
            $this->name,
            $this->shortcut,
            $this->mode,
            $this->description,
            $default
        );
    }
}
