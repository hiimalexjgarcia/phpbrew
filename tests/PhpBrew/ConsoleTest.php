<?php

namespace PhpBrew\Tests;

use PhpBrew\Console;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class ConsoleTest extends TestCase
{
    public function testGetLongVersionIncludesSymfonyConsole()
    {
        $console = Console::getInstance();
        $longVersion = $console->getLongVersion();

        $this->assertStringContainsString(Console::NAME, $longVersion);
        $this->assertStringContainsString(Console::VERSION, $longVersion);
        $this->assertStringContainsString('symfony/console:', $longVersion);
    }

    /**
     * @dataProvider preprocessArgvProvider
     */
    public function testPreprocessArgv(array $input, array $expected)
    {
        $console = Console::getInstance();
        $this->assertSame($expected, $console->preprocessArgv($input));
    }

    public static function preprocessArgvProvider()
    {
        return array(
            'space-separated ext subcommand' => array(
                array('phpbrew', 'ext', 'show', 'xdebug'),
                array('phpbrew', 'extension:show', 'xdebug'),
            ),
            'space-separated extension subcommand' => array(
                array('phpbrew', 'extension', 'install', 'apcu', 'latest'),
                array('phpbrew', 'extension:install', 'apcu', 'latest'),
            ),
            'space-separated fpm subcommand' => array(
                array('phpbrew', 'fpm', 'setup'),
                array('phpbrew', 'fpm:setup'),
            ),
            'install with variants and options' => array(
                array('phpbrew', 'install', '8.4.1', '+default', '-pdo', '-n', '10', '-j', '4', '--name=custom'),
                array('phpbrew', 'install', '-n', '10', '-j', '4', '--name=custom', '--', '8.4.1', '+default', '-pdo'),
            ),
            'install alias with double-dash passthrough' => array(
                array('phpbrew', 'i', '8.4.1', '+default', '--', '--with-openssl'),
                array('phpbrew', 'i', '--', '8.4.1', '+default', '--with-openssl'),
            ),
        );
    }

    public function testListWithDirAndVariantsShortcuts()
    {
        $console = Console::getInstance();
        $output = new BufferedOutput();

        $result = $console->runWithTry(array('phpbrew', 'list', '-d', '-v'), $output);
        $this->assertTrue($result);
    }

    public function testInstallHelpOption()
    {
        $console = Console::getInstance();
        $output = new BufferedOutput();

        $result = $console->runWithTry(array('phpbrew', 'install', '--help'), $output);
        $this->assertTrue($result);
        $this->assertStringContainsString('Install php', $output->fetch());
    }

    public function testHelpCommandWithTarget()
    {
        $console = Console::getInstance();
        $output = new BufferedOutput();

        $result = $console->runWithTry(array('phpbrew', 'help', 'install'), $output);
        $this->assertTrue($result);
        $this->assertStringContainsString('Install php', $output->fetch());
    }

    public function testUnknownCommandReturnsFalse()
    {
        $console = Console::getInstance();
        $output = new BufferedOutput();

        $result = $console->runWithTry(array('phpbrew', 'nonexistent-command'), $output);
        $this->assertFalse($result);
        $this->assertStringContainsString('nonexistent-command', $output->fetch());
    }

    public function testVirtualCommandPrintsBashrcHintCleanly()
    {
        $console = Console::getInstance();
        $output = new BufferedOutput();

        $result = $console->runWithTry(array('phpbrew', 'switch', 'php-8.4.0'), $output);
        $this->assertFalse($result);
        $this->assertStringContainsString('bashrc', $output->fetch());
    }
}
