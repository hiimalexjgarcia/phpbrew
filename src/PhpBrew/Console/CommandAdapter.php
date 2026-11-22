<?php

namespace PhpBrew\Console;

use PhpBrew\Command\BaseCommand;
use PhpBrew\Console;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandAdapter extends SymfonyCommand
{
    /**
     * @var BaseCommand
     */
    protected $wrappedCommand;

    /**
     * @var Console
     */
    protected $console;

    /**
     * @var ArgumentDefinition[]
     */
    protected $argDefs = array();

    /**
     * @var SymfonyCommand|null
     */
    protected $helpCommandTarget;

    public function setCommand(SymfonyCommand $command): void
    {
        $this->helpCommandTarget = $command;
    }

    public function __construct(Console $console, BaseCommand $wrappedCommand, $name)
    {
        $this->console = $console;
        $this->wrappedCommand = $wrappedCommand;
        $this->wrappedCommand->setName($name);
        $this->wrappedCommand->setApplication($console);

        parent::__construct($name);
    }

    public function getWrappedCommand()
    {
        return $this->wrappedCommand;
    }

    protected function configure(): void
    {
        $brief = $this->wrappedCommand->brief();
        if ($brief) {
            $this->setDescription($brief);
        }

        $usage = $this->wrappedCommand->usage();
        if ($usage) {
            $this->setHelp($usage);
        }

        $aliases = $this->wrappedCommand->aliases();
        if (!empty($aliases)) {
            $this->setAliases($aliases);
        }

        $optCollection = new OptionCollection();
        $this->wrappedCommand->options($optCollection);
        $definition = $this->getDefinition();
        foreach ($optCollection->getOptions() as $optDef) {
            if (!$definition->hasOption($optDef->getName())) {
                $definition->addOption($optDef->toInputOption());
            }
        }

        $argCollection = new ArgumentCollection();
        $this->wrappedCommand->arguments($argCollection);
        $this->argDefs = $argCollection->getArguments();
        foreach ($this->argDefs as $argDef) {
            $definition->addArgument($argDef->toInputArgument());
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->helpCommandTarget !== null) {
            $helper = new DescriptorHelper();
            $helper->describe($output, $this->helpCommandTarget);
            $this->helpCommandTarget = null;

            return 0;
        }

        $logger = $this->console->getLogger();
        $logger->setOutput($output);

        $optionResult = OptionResult::fromInput($input);
        $this->console->options = $optionResult;
        $this->wrappedCommand->logger = $logger;
        $this->wrappedCommand->formatter = $this->console->getFormatter();
        $this->wrappedCommand->options = $optionResult;

        if ($this->wrappedCommand->prepare() === false) {
            return 1;
        }

        $args = array();
        foreach ($this->argDefs as $argDef) {
            $val = $input->getArgument($argDef->getName());
            if ($argDef->isArray()) {
                if (is_array($val)) {
                    foreach ($val as $item) {
                        $args[] = $item;
                    }
                }
            } elseif ($val !== null) {
                $args[] = $val;
            }
        }

        $result = call_user_func_array(array($this->wrappedCommand, 'execute'), $args);

        return ($result === false) ? 1 : 0;
    }
}
