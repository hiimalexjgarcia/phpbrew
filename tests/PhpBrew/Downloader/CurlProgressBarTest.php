<?php

namespace PhpBrew\Tests\Downloader;

use CurlKit\Progress\CurlProgressInterface;
use PhpBrew\Downloader\CurlProgressBar;
use PHPUnit\Framework\TestCase;

class CurlProgressBarTest extends TestCase
{
    public function testImplementsCurlProgressInterfaceAndResets()
    {
        $bar = new CurlProgressBar();
        $this->assertInstanceOf(CurlProgressInterface::class, $bar);
        $this->assertFalse($bar->done);

        // Zero downloadSize should return early without changing done state
        $bar->curlCallback(null, 0, 0, 0, 0);
        $this->assertFalse($bar->done);

        $bar->done = true;
        $bar->reset();
        $this->assertFalse($bar->done);
    }
}
