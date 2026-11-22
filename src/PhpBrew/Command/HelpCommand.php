<?php

namespace PhpBrew\Command;

use Symfony\Component\Console\Helper\DescriptorHelper;

class HelpCommand extends BaseCommand
{
    public function brief()
    {
        return 'Display help for a command';
    }

    public function arguments($args)
    {
        $args->add('command_name', 'The command name')->optional();
    }

    public function execute($commandName = null)
    {
        if ($commandName === null) {
            $headline = <<<EOS
  ______ _   _ ____________                   
  | ___ \ | | || ___ \ ___ \                  
  | |_/ / |_| || |_/ / |_/ /_ __ _____      __
  |  __/|  _  ||  __/| ___ \ '__/ _ \ \ /\ / /
  | |   | | | || |   | |_/ / | |  __/\ V  V / 
  \_|   \_| |_/\_|   \____/|_|  \___| \_/\_/  
                                            

EOS;
            $this->logger->write($this->formatter->format($headline, 'strong_white'));

            $helper = new DescriptorHelper();
            $helper->describe($this->logger->getOutput(), $this->application);

            return true;
        }

        $cmd = $this->application->find($commandName);
        $helper = new DescriptorHelper();
        $helper->describe($this->logger->getOutput(), $cmd);

        return true;
    }
}
