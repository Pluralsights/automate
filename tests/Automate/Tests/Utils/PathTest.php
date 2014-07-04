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
use Automate\Utils\Path;

class PathTest extends \PHPUnit_Framework_TestCase
{

    public function testNormalize()
    {
        $this->assertEquals('c:/some/other.txt', Path::normalize('C:\\some\other.txt'));
        $this->assertEquals('c:/other.txt', Path::normalize('c:\\some\..\other.txt'));
        $this->assertEquals('../other.txt', Path::normalize('..\other.txt'));
        $this->assertEquals('../other.txt', Path::normalize('..\other.txt'));
        $this->assertEquals('/home/new', Path::normalize('/home/other/../new'));
        $this->assertEquals('/home/other/new', Path::normalize('/home/other/./new'));
        $this->assertEquals('protocol://home/other.txt', Path::normalize('protocol://home/other.txt'));

    }

    public function testIsAbsolute()
    {
        $this->assertTrue(Path::isAbsolute('/home/path'));
        $this->assertFalse(Path::isAbsolute('home/path'));
        $this->assertFalse(Path::isAbsolute('../home/path'));
        $this->assertTrue(Path::isAbsolute('protocol://home/path'));
    }

    public function testGetFilesList()
    {
        $fixtureFolder = dirname(dirname(__FILE__)) . '/Fixtures/folder';

        $list = Path::getFilesList($fixtureFolder);
        $this->assertEquals(5, $list->count());

        $list = Path::getFilesList($fixtureFolder, array('folder2'));
        $this->assertEquals(3, $list->count());

        $list = Path::getFilesList($fixtureFolder, array('file1'));
        $this->assertEquals(4, $list->count());

        $list = Path::getFilesList($fixtureFolder, array('folder2/*'));
        $this->assertEquals(4, $list->count());

        $list = Path::getFilesList($fixtureFolder, array('folder2/file2'));
        $this->assertEquals(4, $list->count());
    }

}
