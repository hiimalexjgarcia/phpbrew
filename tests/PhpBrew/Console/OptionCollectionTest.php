<?php

namespace PhpBrew\Tests\Console;

use PhpBrew\Console\Option;
use PhpBrew\Console\OptionCollection;
use PhpBrew\Console\OptionResult;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;

class OptionCollectionTest extends TestCase
{
    public function testAddParsesGetOptionKitSpecs()
    {
        $collection = new OptionCollection();
        $collection->add('d|dir', 'Show directory');
        $collection->add('name:', 'Build name');
        $collection->add('s|sapi:=string', 'SAPI name');
        $collection->add('patch+:', 'Patch files');
        $collection->add('o|old?', 'Old versions');
        $collection->add('so|show-options', 'Show options');

        $options = $collection->getOptions();

        $dirOpt = $options['dir']->toInputOption();
        $this->assertSame('d', $dirOpt->getShortcut());
        $this->assertFalse($dirOpt->acceptValue());

        $nameOpt = $options['name']->toInputOption();
        $this->assertNull($nameOpt->getShortcut());
        $this->assertTrue($nameOpt->isValueRequired());

        $sapiOpt = $options['sapi']->toInputOption();
        $this->assertSame('s', $sapiOpt->getShortcut());
        $this->assertTrue($sapiOpt->isValueRequired());

        $patchOpt = $options['patch']->toInputOption();
        $this->assertTrue($patchOpt->isValueRequired());
        $this->assertTrue($patchOpt->isArray());

        $oldOpt = $options['old']->toInputOption();
        $this->assertSame('o', $oldOpt->getShortcut());
        $this->assertTrue($oldOpt->isValueOptional());

        $showOpt = $options['show-options']->toInputOption();
        $this->assertNull($showOpt->getShortcut());
        $this->assertFalse($showOpt->acceptValue());
    }

    public function testOptionResultAccessors()
    {
        $result = new OptionResult(array('name' => 'custom', 'force' => false));

        $this->assertTrue($result->has('name'));
        $this->assertFalse($result->has('force'));
        $this->assertFalse($result->has('missing'));

        $this->assertSame('custom', $result->get('name'));
        $this->assertSame('custom', $result->name);
        $this->assertNull($result->get('missing'));
        $this->assertTrue(isset($result->name));
        $this->assertFalse(isset($result->missing));

        $result['force']->setValue(true);
        $this->assertTrue($result->has('force'));
        $this->assertTrue($result->force);

        $result->set('jobs', new Option('jobs', '4'));
        $this->assertSame('4', $result->get('jobs'));
    }

    public function testOptionResultFromInput()
    {
        $collection = new OptionCollection();
        $collection->add('f|force', 'Force');
        $collection->add('name:', 'Name');

        $def = new InputDefinition();
        foreach ($collection->getOptions() as $opt) {
            $def->addOption($opt->toInputOption());
        }

        $input = new ArrayInput(array('--force' => true, '--name' => 'my-php'), $def);
        $result = OptionResult::fromInput($input);

        $this->assertTrue($result->has('force'));
        $this->assertSame('my-php', $result->get('name'));
    }
}
