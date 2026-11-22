<?php

namespace PhpBrew\Console;

use ArrayAccess;
use Symfony\Component\Console\Input\InputInterface;

class OptionResult implements ArrayAccess
{
    /**
     * @var array<string, Option>
     */
    protected $keys = array();

    public function __construct(array $values = array())
    {
        foreach ($values as $name => $value) {
            $this->set($name, $value);
        }
    }

    public static function fromInput(InputInterface $input)
    {
        $result = new self();
        foreach ($input->getOptions() as $name => $value) {
            if ($value !== null && $value !== false) {
                $result->set($name, $value);
            }
        }
        return $result;
    }

    public function has($name)
    {
        return isset($this->keys[$name])
            && $this->keys[$name]->getValue() !== null
            && $this->keys[$name]->getValue() !== false;
    }

    public function get($name)
    {
        return isset($this->keys[$name]) ? $this->keys[$name]->getValue() : null;
    }

    public function set($name, $value)
    {
        if ($value instanceof Option) {
            $this->keys[$name] = $value;
        } else {
            $this->keys[$name] = new Option($name, $value);
        }
        return $this;
    }

    public function __get($name)
    {
        if (isset($this->keys[$name])) {
            return $this->keys[$name]->getValue();
        }
        return null;
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __isset($name)
    {
        return $this->has($name);
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->keys[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (!isset($this->keys[$offset])) {
            $this->keys[$offset] = new Option($offset);
        }
        return $this->keys[$offset];
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->keys[$offset]);
    }

    public function toArray()
    {
        $data = array();
        foreach ($this->keys as $name => $option) {
            $data[$name] = $option->getValue();
        }
        return $data;
    }
}
