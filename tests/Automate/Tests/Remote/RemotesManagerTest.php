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

use Automate\Remote\Remote;
use Automate\Remote\RemotesManager;

class RemotesManagerTest extends \PHPUnit_Framework_TestCase
{

    public function testRemote()
    {
        $remoteManager = new RemotesManager();
        $remoteManager->add(new Remote('host1.domain.ltd', 'user', 'password'));
        $remoteManager->add(new Remote('host2.domain.ltd', 'user', 'password'));

        $this->assertEquals(2, count($remoteManager->getRemotes()));

        foreach ($remoteManager->getRemotes() as $remote) {
            $this->assertInstanceOf('Automate\Remote\Remote', $remote);
        }

    }

    public function testGroup()
    {
        $remoteManager = new RemotesManager();

        $remoteManager->add(new Remote('host1.domain.ltd', 'user', 'password', '1'));
        $remoteManager->add(new Remote('host2.domain.ltd', 'user', 'password', array('1', '2')));

        $this->assertEquals(2, count($remoteManager->getGroup('1')));
        $this->assertEquals(1, count($remoteManager->getGroup('2')));
        $this->assertTrue($remoteManager->hasGroup('1'));
        $this->assertTrue($remoteManager->hasGroup('2'));
        $this->assertFalse($remoteManager->hasGroup('3'));

    }

    public function testMaster()
    {
        $remoteManager = new RemotesManager();

        $remoteManager->add(new Remote('host1.domain.ltd', 'user', 'password', '1'));

        $this->assertFalse($remoteManager->hasMaster());
        $this->assertNull($remoteManager->getMaster());

        $remoteManager->add(new Remote('host2.domain.ltd', 'user', 'password', array('1', '2'), true));

        $this->assertTrue($remoteManager->hasMaster());
        $this->assertEquals('host2.domain.ltd', $remoteManager->getMaster()->getHost());
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testMultipleMaster()
    {
        $remoteManager = new RemotesManager();

        $remoteManager->add(new Remote('host1.domain.ltd', 'user', 'password', '1', true));
        $remoteManager->add(new Remote('host2.domain.ltd', 'user', 'password', array('1', '2'), true));
    }

}
