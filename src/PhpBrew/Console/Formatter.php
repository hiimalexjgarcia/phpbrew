<?php

namespace PhpBrew\Console;

class Formatter
{
    /**
     * @var array<string, mixed>
     */
    protected $styles = array();

    /**
     * @var bool
     */
    protected $rawOutput = false;

    public function addStyle($name, $style)
    {
        $this->styles[$name] = $style;
    }

    public function preferRawOutput($raw = true)
    {
        $this->rawOutput = (bool) $raw;
    }

    public function format($text, $style = null)
    {
        if ($this->rawOutput || $style === null) {
            return (string) $text;
        }

        return (string) $text;
    }
}
