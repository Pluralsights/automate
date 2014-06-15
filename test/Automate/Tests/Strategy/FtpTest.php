<?php

/*
 * This file is part of the Automate package.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Automate\Tests\Task;

use Automate\Context\Context;
use Automate\Strategy\Ftp;

class FtpTest extends \PHPUnit_Framework_TestCase
{

    public function testDeploy()
    {
        $ftp = new Ftp();
        $context = new Context();
        $tasksManager = $this->getMock('Automate\Task\TasksManager');
        $tasksManager->expects($this->any())
            ->method('run')
            ->with($this->equalTo('remote:upload'),$this->equalTo(array(
                'from' => './',
                'to' => '/path/remote/releases_dir/1234',
                'group' => 'web',
                'excludes' => array('exclude'),
            )))
        ;

        $context->setTasksManager($tasksManager);
        $ftp->setContext($context);

        $this->assertEquals('ftp', $ftp->getName());

        $ftp->deploy('1234', array(
            'releases_dir' => 'releases_dir',
            'to' => '/path/remote',
            'from' => './',
            'group' => 'web',
            'excludes' => array('exclude'),
        ));

    }

}
