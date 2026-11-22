<?php

namespace PhpBrew\Console;

class Prompter
{
    public function ask($prompt, array $validValues = array(), $default = null)
    {
        $suffix = '';
        if (!empty($validValues)) {
            $suffix = ' [' . implode('/', $validValues) . ']';
        }
        fwrite(STDOUT, $prompt . $suffix . ' ');
        $line = fgets(STDIN);
        if ($line === false) {
            return $default;
        }
        $answer = trim($line);
        if ($answer === '' && $default !== null) {
            return $default;
        }
        return $answer;
    }
}
