<?php

namespace PhpBrew\Console;

use Symfony\Component\Console\Input\InputOption;

class OptionCollection
{
    /**
     * @var OptionDefinition[]
     */
    protected $options = array();

    /**
     * Parse a GetOptionKit option specification (e.g. 'd|dir', 's|sapi:=string', 'patch+:').
     *
     * @param string $spec
     * @param string $description
     *
     * @return OptionDefinition
     */
    public function add($spec, $description = '')
    {
        // Strip type suffix like '=string', '=number', '=file', '=dir'
        $cleanSpec = preg_replace('/=[a-zA-Z0-9_]+$/', '', $spec);

        $mode = InputOption::VALUE_NONE;
        if (substr($cleanSpec, -2) === '+:') {
            $mode = InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY;
            $cleanSpec = substr($cleanSpec, 0, -2);
        } elseif (substr($cleanSpec, -1) === ':') {
            $mode = InputOption::VALUE_REQUIRED;
            $cleanSpec = substr($cleanSpec, 0, -1);
        } elseif (substr($cleanSpec, -1) === '?') {
            $mode = InputOption::VALUE_OPTIONAL;
            $cleanSpec = substr($cleanSpec, 0, -1);
        } elseif (substr($cleanSpec, -1) === '+') {
            $mode = InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY;
            $cleanSpec = substr($cleanSpec, 0, -1);
        }

        $shortcut = null;
        $name = $cleanSpec;
        if (strpos($cleanSpec, '|') !== false) {
            list($first, $second) = explode('|', $cleanSpec, 2);
            if (strlen($first) === 1) {
                $shortcut = $first;
                $name = $second;
            } elseif (strlen($second) === 1) {
                $shortcut = $second;
                $name = $first;
            } else {
                // Multi-char short option (e.g. 'so|show-options'): use long name
                $name = $second;
            }
        }

        $def = new OptionDefinition($name, $shortcut, $mode, $description);
        $this->options[$name] = $def;

        return $def;
    }

    /**
     * @return OptionDefinition[]
     */
    public function getOptions()
    {
        return $this->options;
    }
}
