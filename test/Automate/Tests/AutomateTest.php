<?php

/*
 * This file is part of the Automate package.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Automate\Tests\Remote;

use Automate\Automate;

class AutomateTest extends \PHPUnit_Framework_TestCase
{

    public function testInit()
    {

        $application = new Automate();

        $context = \PHPUnit_Framework_Assert::readAttribute($application, 'context');

        $this->assertInstanceOf('Automate\Local\Local', $context->getLocal());
        $this->assertInstanceOf('Automate\Remote\RemotesManager', $context->getRemoteManager());
        $this->assertInstanceOf('Automate\Automate', $context->getApp());
        $this->assertInstanceOf('Automate\Strategy\StrategiesManager', $context->getStrategiesManager());

    }

}
