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

class RemotesTest extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {

        $remote = $this->getRemoteMock(false, 'host1.domain.ltd', 'user', 'password', '1', false);

        $this->assertEquals('host1.domain.ltd', $remote->getHost());
        $this->assertEquals('user', $remote->getUser());
        $this->assertEquals('password', \PHPUnit_Framework_Assert::readAttribute($remote, 'password'));
        $this->assertEquals(array('1'), $remote->getGroups());
        $this->assertFalse($remote->getIsMaster());
        $this->assertFalse($remote->getIsConnected());
    }

    public function testCd()
    {

        $remote = $this->getRemoteMock(false);

        $remote->cd('/home/wwwroot/myapp');

        $this->assertEquals('/home/wwwroot/myapp', \PHPUnit_Framework_Assert::readAttribute($remote, 'cd'));

    }

    public function testConnect()
    {
        $remote = $this->getRemoteMock(true);

        $remote->connect();
        $remote->connect(); // call only one Net_SFTP:login

        $this->assertTrue($remote->getIsConnected());

    }

    /**
     * @expectedException Automate\Exception\RemoteException
     */
    public function testErrorConnect()
    {
        $remote = $this->getMock('Automate\Remote\Remote', array('getSFTP'), array('host1.domain.ltd', 'user', 'password'));

        $sftp = $this->getMock('Net_SFTP', array('login'), array(), '', false, false, false);

        $sftp
            ->expects($this->once())
            ->method('login')
            ->will($this->returnValue(false))
        ;

        $remote
            ->expects($this->any())
            ->method('getSFTP')
            ->will($this->returnValue($sftp))
        ;

        $this->assertFalse($remote->getIsConnected());

        $remote->connect();

        $this->assertTrue($remote->getIsConnected());

    }

    public function testKeyConnect()
    {
        $remote = $this->getRemoteMock(true, 'host1.domain.ltd', 'user', $this->getMock('Automate\Utils\Remote\Key'));

        $remote->connect();

        $this->assertTrue($remote->getIsConnected());

    }

    public function testExecute()
    {
        $remote = $this->getRemoteMock();

        $this->assertEquals('command test', $remote->execute('command test'));

        $remote->cd('/path/to/project');

        $this->assertEquals('cd /path/to/project && command test', $remote->execute('command test'));
        $this->assertEquals('cd /path/to/project && command2 test', $remote->execute('command2 test'));

    }

    /**
     * @expectedException Automate\Exception\RemoteException
     */
    public function testExecuteError()
    {

        $remote = $this->getMock('Automate\Remote\Remote', array('getSFTP'), array('host1.domain.ltd', 'user', 'password'));

        $sftp = $this->getMock('Net_SFTP', array('login', 'exec', 'getStdError'), array(), '', false, false, false);

        $sftp
            ->expects($this->once())
            ->method('login')
            ->will($this->returnValue(true))
        ;

        $sftp
            ->expects($this->any())
            ->method('exec')
            ->will($this->returnArgument(0))
        ;

        $sftp
            ->expects($this->any())
            ->method('getStdError')
            ->will($this->returnValue('error'))
        ;

        $remote
            ->expects($this->any())
            ->method('getSFTP')
            ->will($this->returnValue($sftp))
        ;

        $remote->execute('test');

    }

    public function testUpload()
    {
        $remote = $this->getMock('Automate\Remote\Remote', array('getSFTP'), array('host1.domain.ltd', 'user', 'password'));
        $sftp   = $this->getMock('Net_SFTP', array('put', 'login', 'mkdir'), array(), '', false, false, false);

        $sftp
            ->expects($this->once())
            ->method('login')
            ->will($this->returnValue(true))
        ;

        $sftp
            ->expects($this->any())
            ->method('put')
            ->will($this->returnValue(true))
        ;

        $sftp
            ->expects($this->once())
            ->method('mkdir')
            ->will($this->returnValue(true))
        ;

        $remote
            ->expects($this->once())
            ->method('getSFTP')
            ->will($this->returnValue($sftp))
        ;

        $remote->uploadFile('./test.txt', '/path/to/project/test.txt');
        $remote->uploadFile('./test2.txt', '/path/to/project/test2.txt');
    }

    /**
     * @expectedException Automate\Exception\RemoteException
     */
    public function testUploadError()
    {
        $remote = $this->getMock('Automate\Remote\Remote', array('getSFTP'), array('host1.domain.ltd', 'user', 'password'));
        $sftp   = $this->getMock('Net_SFTP', array('put', 'login', 'mkdir', 'getSFTPErrors'), array(), '', false, false, false);

        $sftp
            ->expects($this->once())
            ->method('login')
            ->will($this->returnValue(true))
        ;

        $sftp
            ->expects($this->any())
            ->method('put')
            ->will($this->returnValue(false))
        ;

        $sftp
            ->expects($this->once())
            ->method('mkdir')
            ->will($this->returnValue(true))
        ;

        $sftp
            ->expects($this->any())
            ->method('getSFTPErrors')
            ->will($this->returnValue(array('error')))
        ;

        $remote
            ->expects($this->once())
            ->method('getSFTP')
            ->will($this->returnValue($sftp))
        ;

        $remote->uploadFile('./test.txt', '/path/to/project/test.txt');

    }

    private function getRemoteMock($mockSFTP = true, $host = 'host1.domain.ltd', $user = 'user', $password = 'password', $groups = 'group', $isMaster = false)
    {
        $remote = $this->getMock('Automate\Remote\Remote', array('getSFTP'), array($host, $user, $password, $groups, $isMaster));
        $sftp   = $this->getMock('Net_SFTP', array('exec', 'login', 'getStdError'), array(), '', false, false, false);

        if ($mockSFTP) {

            $sftp
                ->expects($this->once())
                ->method('login')
                ->will($this->returnValue(true))
            ;

            $sftp
                ->expects($this->any())
                ->method('exec')
                ->will($this->returnArgument(0))

            ;

            $remote
                ->expects($this->any())
                ->method('getSFTP')
                ->will($this->returnValue($sftp))
            ;

        }

        return $remote;
    }

}
