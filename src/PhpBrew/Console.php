<?php

namespace PhpBrew;

use BadMethodCallException;
use Composer\InstalledVersions;
use Exception;
use PhpBrew\Command\BaseCommand;
use PhpBrew\Console\CommandAdapter;
use PhpBrew\Console\Formatter;
use PhpBrew\Console\Logger;
use PhpBrew\Console\OptionCollection;
use PhpBrew\Console\OptionResult;
use PhpBrew\Exception\SystemCommandException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class Console extends Application
{
    const NAME = 'phpbrew';
    const VERSION = '3.0.0-beta1';

    /**
     * @var Console|null
     */
    protected static $instance;

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
    protected $formatter;

    /**
     * @var array<string, array<string, string>>
     */
    protected $subcommandMap = array();

    public function __construct()
    {
        $this->logger = new Logger();
        $this->options = new OptionResult();
        $this->formatter = new Formatter();

        parent::__construct(self::NAME, self::VERSION);
        $this->setAutoExit(false);
        $this->setCatchExceptions(false);

        $this->init();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function getFormatter()
    {
        return $this->formatter;
    }

    public function getLongVersion(): string
    {
        $version = parent::getLongVersion();
        if (class_exists(InstalledVersions::class) && InstalledVersions::isInstalled('symfony/console')) {
            $consoleVersion = InstalledVersions::getPrettyVersion('symfony/console');
            if ($consoleVersion !== null) {
                $version .= PHP_EOL . sprintf('symfony/console: <info>%s</info>', ltrim($consoleVersion, 'v'));
            }
        }

        return $version;
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        $definition = parent::getDefaultInputDefinition();
        $options = $definition->getOptions();
        // Remove -v shortcut from --verbose so commands like `list` can use `-v` for `--variants`
        if (isset($options['verbose'])) {
            $options['verbose'] = new InputOption(
                'verbose',
                null,
                InputOption::VALUE_NONE,
                'Increase the verbosity of messages'
            );
        }
        // Remove -n shortcut from --no-interaction so `install` can use `-n` for `--nice`
        if (isset($options['no-interaction'])) {
            $options['no-interaction'] = new InputOption(
                'no-interaction',
                null,
                InputOption::VALUE_NONE,
                'Do not ask any interactive question'
            );
        }
        $definition->setOptions($options);
        $definition->addOption(
            new InputOption('no-progress', null, InputOption::VALUE_NONE, 'Do not display progress bar.')
        );
        $definition->addOption(new InputOption('debug', null, InputOption::VALUE_NONE, 'Display debug messages.'));

        return $definition;
    }

    public function options($opts)
    {
        $opts->add('no-progress', 'Do not display progress bar.');
    }

    public function init()
    {
        $this->command('init');
        $this->command('known');
        $this->command('install');
        $this->command('list');
        $this->command('use');
        $this->command('switch');
        $this->command('each');

        $this->command('config');
        $this->command('info');
        $this->command('env');
        $this->command('extension');
        $this->command('variants');
        $this->command('path');
        $this->command('cd');
        $this->command('download');
        $this->command('clean');
        $this->command('update');
        $this->command('ctags');
        $this->command('help');

        $this->command('fpm');

        $this->command('list-ini', 'PhpBrew\Command\ListIniCommand');
        $this->command('self-update', 'PhpBrew\Command\SelfUpdateCommand');

        $this->command('remove');
        $this->command('purge');

        $this->command('off');
        $this->command('switch-off', 'PhpBrew\Command\SwitchOffCommand');

        $this->command('system');
        $this->command('system-off');

        $this->configure();

        // We use '#' as the prefix to prevent issue with bash
        if (!extension_loaded('json')) {
            $this->logger->warn('# WARNING: json extension is required for parsing release info.');
        }
        if (!extension_loaded('libxml')) {
            $this->logger->warn('# WARNING: libxml extension is required for parsing pecl package file.');
        }
        if (!extension_loaded('ctype')) {
            $this->logger->warn('# WARNING: ctype extension might be required for parsing yaml file.');
        }
    }

    public function command($name, $className = null)
    {
        if ($className === null) {
            $parts = array_map('ucfirst', explode('-', $name));
            $className = 'PhpBrew\\Command\\' . implode('', $parts) . 'Command';
        }

        /** @var BaseCommand $cmd */
        $cmd = new $className($name);
        $cmd->setApplication($this);
        $cmd->init();

        $adapter = new CommandAdapter($this, $cmd, $name);
        $this->add($adapter);

        $subcommands = $cmd->getSubcommands();
        if (!empty($subcommands)) {
            $this->subcommandMap[$name] = array();
            foreach ($cmd->aliases() as $alias) {
                $this->subcommandMap[$alias] = array();
            }

            foreach ($subcommands as $subName => $subClass) {
                /** @var BaseCommand $subCmd */
                $subCmd = new $subClass($subName);
                $subCmd->setApplication($this);
                $subCmd->init();

                $fullSubName = $name . ':' . $subName;
                $subAdapter = new CommandAdapter($this, $subCmd, $fullSubName);
                $this->add($subAdapter);

                $this->subcommandMap[$name][$subName] = $fullSubName;
                foreach ($cmd->aliases() as $alias) {
                    $this->subcommandMap[$alias][$subName] = $fullSubName;
                }
            }
        }

        return $adapter;
    }

    public function configure()
    {
        // avoid warnings when web scraping possible malformed HTML from pecl
        if (extension_loaded('libxml')) {
            libxml_use_internal_errors(true);
        }
        // prevent execution time limit fatal error
        set_time_limit(0);

        // prevent warnings when timezone is not set
        date_default_timezone_set(Utils::readTimeZone() ?: 'America/Los_Angeles');

        // fix bold output so it looks good on light and dark terminals
        $this->getFormatter()->addStyle('bold', array('bold' => 1));

        $this->logger->levelStyles['warn'] = 'yellow';
        $this->logger->levelStyles['error'] = 'red';
    }

    public function brief()
    {
        return 'brew your latest php!';
    }

    public function preprocessArgv(array $argv)
    {
        $prog = array_shift($argv);
        if (empty($argv)) {
            return array($prog);
        }

        // Map space-separated subcommands: e.g. `ext show` -> `extension:show`, `fpm setup` -> `fpm:setup`
        if (
            isset($this->subcommandMap[$argv[0]])
            && isset($argv[1])
            && isset($this->subcommandMap[$argv[0]][$argv[1]])
        ) {
            $full = $this->subcommandMap[$argv[0]][$argv[1]];
            array_splice($argv, 0, 2, array($full));
        }

        // For `install`, allow `-variant` tokens (e.g. `-pdo`) without Symfony treating them as unknown options
        if (in_array($argv[0], array('install', 'i', 'ins'), true) && $this->has('install')) {
            $cmd = $this->find('install');
            $def = $cmd->getDefinition();
            $appDef = $this->getDefinition();

            $knownOpts = array();
            $positional = array();
            $cmdName = array_shift($argv);
            $afterDoubleDash = false;

            $count = count($argv);
            for ($i = 0; $i < $count; $i++) {
                $token = $argv[$i];
                if ($afterDoubleDash) {
                    $positional[] = $token;
                    continue;
                }
                if ($token === '--') {
                    $afterDoubleDash = true;
                    continue;
                }
                if (substr($token, 0, 2) === '--') {
                    $optName = substr($token, 2);
                    $eqPos = strpos($optName, '=');
                    $baseName = ($eqPos !== false) ? substr($optName, 0, $eqPos) : $optName;
                    $opt = $def->hasOption($baseName)
                        ? $def->getOption($baseName)
                        : ($appDef->hasOption($baseName) ? $appDef->getOption($baseName) : null);
                    if ($opt !== null) {
                        $knownOpts[] = $token;
                        if ($eqPos === false && $opt->acceptValue() && isset($argv[$i + 1])) {
                            $knownOpts[] = $argv[++$i];
                        }
                        continue;
                    }
                } elseif (substr($token, 0, 1) === '-' && strlen($token) >= 2) {
                    $short = substr($token, 1, 1);
                    $opt = $def->hasShortcut($short)
                        ? $def->getOptionForShortcut($short)
                        : ($appDef->hasShortcut($short) ? $appDef->getOptionForShortcut($short) : null);
                    if ($opt !== null && strlen($token) === 2) {
                        $knownOpts[] = $token;
                        if ($opt->acceptValue() && isset($argv[$i + 1])) {
                            $knownOpts[] = $argv[++$i];
                        }
                        continue;
                    }
                }
                $positional[] = $token;
            }

            $argv = array_merge(array($cmdName), $knownOpts);
            if (!empty($positional)) {
                $argv[] = '--';
                $argv = array_merge($argv, $positional);
            }
        }

        return array_merge(array($prog), $argv);
    }

    public function doRun(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->setOutput($output);
        if ($input->hasParameterOption(array('--quiet', '-q'), true)) {
            $this->logger->setQuiet();
        }
        if (
            $input->hasParameterOption(array('--debug', '-d'), true)
            || $input->hasParameterOption(array('-vvv'), true)
        ) {
            $this->logger->setDebug();
        }

        return parent::doRun($input, $output);
    }

    public function runWithTry(array $argv, ?OutputInterface $output = null)
    {
        $output = $output ?: new ConsoleOutput();
        $processedArgv = $this->preprocessArgv($argv);
        $input = new ArgvInput($processedArgv);

        try {
            $exitCode = $this->run($input, $output);

            return $exitCode === 0;
        } catch (CommandNotFoundException $e) {
            $this->logger->error($e->getMessage());
            $this->logger->newline();
            $this->logger->writeln('Please try the command below to see the details:');
            $this->logger->newline();
            $this->logger->writeln("\t" . self::NAME . ' help');
            $this->logger->newline();
        } catch (SystemCommandException $e) {
            $buildLog = $e->getLogFile();
            $this->logger->error('Error: ' . trim($e->getMessage()));

            if ($buildLog !== null && file_exists($buildLog)) {
                $this->logger->error('The last 5 lines in the log file:');
                $lines = array_slice(file($buildLog), -5);
                foreach ($lines as $line) {
                    echo $line, PHP_EOL;
                }
                $this->logger->error('Please checkout the build log file for more details:');
                $this->logger->error("\t tail $buildLog");
            }
        } catch (BadMethodCallException $e) {
            $this->logger->error($e->getMessage());
            $this->logger->error('Seems like an application logic error, please contact the developer.');
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            if ($this->logger->isDebug()) {
                $this->logger->writeln($e->getTraceAsString());
            }
        }

        return false;
    }
}
