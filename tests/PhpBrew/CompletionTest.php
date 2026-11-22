<?php

namespace PhpBrew\Tests;

use PhpBrew\Testing\CommandTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CompletionTest extends CommandTestCase
{
    /**
     * @dataProvider completionProvider
     */
    public function testCompletion($shell)
    {
        $command = $this->app->find('completion');
        $tester = new CommandTester($command);
        $exitCode = $tester->execute(array('shell' => $shell));

        $this->assertSame(0, $exitCode);
        $this->assertNotEmpty($tester->getDisplay());
    }

    public static function completionProvider()
    {
        return array(
            'bash' => array('bash'),
            'zsh' => array('zsh'),
        );
    }
}
