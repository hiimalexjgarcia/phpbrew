<?php

namespace PhpBrew\Tests\Console;

use PhpBrew\Console\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class LoggerTest extends TestCase
{
    public function testVerbosityLevels()
    {
        $output = new BufferedOutput();
        $logger = new Logger($output);

        $this->assertFalse($logger->isQuiet());
        $this->assertFalse($logger->isDebug());

        $logger->debug('hidden-debug');
        $logger->info('visible-info');
        $logger->notice('visible-notice');
        $logger->warn('visible-warn');
        $logger->error('visible-error');

        $text = $output->fetch();
        $this->assertStringNotContainsString('hidden-debug', $text);
        $this->assertStringContainsString('visible-info', $text);
        $this->assertStringContainsString('visible-notice', $text);
        $this->assertStringContainsString('visible-warn', $text);
        $this->assertStringContainsString('visible-error', $text);
    }

    public function testDebugLevelShowsDebugMessages()
    {
        $output = new BufferedOutput();
        $logger = new Logger($output);
        $logger->setDebug();

        $this->assertTrue($logger->isDebug());
        $logger->debug('shown-debug');
        $this->assertStringContainsString('shown-debug', $output->fetch());
    }

    public function testQuietLevelSuppressesNonDirectMessages()
    {
        $output = new BufferedOutput();
        $logger = new Logger($output);
        $logger->setQuiet();

        $this->assertTrue($logger->isQuiet());
        $logger->info('hidden-info');
        $logger->warn('hidden-warn');
        $logger->error('hidden-error');
        $this->assertSame('', $output->fetch());

        $logger->writeln('direct-line');
        $this->assertStringContainsString('direct-line', $output->fetch());
    }
}
