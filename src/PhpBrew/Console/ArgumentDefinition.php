<?php

namespace PhpBrew\Console;

use Symfony\Component\Console\Input\InputArgument;

class ArgumentDefinition
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var int
     */
    protected $mode;

    /**
     * @var mixed
     */
    protected $default;

    public function __construct($name, $description = '', $mode = InputArgument::REQUIRED, $default = null)
    {
        $this->name = $name;
        $this->description = $description;
        $this->mode = $mode;
        $this->default = $default;
    }

    public function multiple()
    {
        $this->mode = ($this->mode & ~InputArgument::REQUIRED) | InputArgument::OPTIONAL | InputArgument::IS_ARRAY;
        return $this;
    }

    public function optional($default = null)
    {
        $this->mode = ($this->mode & ~InputArgument::REQUIRED) | InputArgument::OPTIONAL;
        $this->default = $default;
        return $this;
    }

    public function required()
    {
        $this->mode = ($this->mode & ~InputArgument::OPTIONAL) | InputArgument::REQUIRED;
        return $this;
    }

    public function isa($type)
    {
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

    public function isArray()
    {
        return (bool) ($this->mode & InputArgument::IS_ARRAY);
    }

    public function toInputArgument()
    {
        return new InputArgument(
            $this->name,
            $this->mode,
            $this->description,
            $this->default
        );
    }
}
