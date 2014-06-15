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

use Automate\Local\Local;

class LocalTest extends \PHPUnit_Framework_TestCase
{

    public function testExecute()
    {
        $local = new Local();

        $this->assertEquals('test', trim($local->execute('echo "test"')));
    }

    /**
     * @expectedException Automate\Exception\LocalException
     */
    public function testExecuteWithError()
    {
        $local = new Local();

        $local->execute('invalid_command_name param1');
    }

}
