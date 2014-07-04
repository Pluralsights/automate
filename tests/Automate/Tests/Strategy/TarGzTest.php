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
use Automate\Strategy\TarGz;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class TarGzTest extends \PHPUnit_Framework_TestCase
{

    public function testDeploy()
    {
        $fixtureFolder = dirname(dirname(__FILE__)) . '/Fixtures/folder';
        $fs = new Filesystem();
        $fs->mkdir('.automate');

        $strategy = new TarGz();
        $context = new Context();
        $context->setOutput(new NullOutput());
        $context->setApp(new Application());

        $tasksManager = $this->getMock('Automate\Task\TasksManager');
        $tasksManager->expects($this->at(0))
            ->method('run')
            ->with($this->equalTo('remote:upload'),$this->equalTo(array(
                'from' => './.automate/release.tar.gz',
                'to' => '/path/remote/releases_dir/1234/release.tar.gz',
                'group' => 'web'
            )))
        ;

        $context->setTasksManager($tasksManager);
        $strategy->setContext($context);

        $this->assertEquals('targz', $strategy->getName());

        $strategy->deploy('1234', array(
            'releases_dir' => 'releases_dir',
            'to' => '/path/remote',
            'from' => $fixtureFolder,
            'group' => 'web',
            'excludes' => array('exclude'),
        ));

        $finder = new Finder();
        $finder
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->in('phar://'. realpath('.automate/release.tar.gz') . '/')
        ;

        $expected = array();
        foreach($finder as $file) {
            $expected[] = $file->getRelativePathname();
        }

        $this->assertContains('folder2', $expected);
        $this->assertContains('folder2'. DIRECTORY_SEPARATOR .'file2', $expected);
        $this->assertContains('folder3', $expected);
        $this->assertContains('folder3'. DIRECTORY_SEPARATOR .'file3', $expected);
        $this->assertContains('file1', $expected);
        $this->assertEquals(5, count($expected));
    }

}
