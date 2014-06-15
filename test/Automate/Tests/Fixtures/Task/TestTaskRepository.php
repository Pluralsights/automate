<?php

/*
 * This file is part of the Automate package.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Automate\Tests\Fixtures\Task;

use Automate\Context\ContextAware;
use Automate\Task\TaskRepositoryInterface;

/**
 * Simple TaskRepository implementation for tests
 */
class TestTaskRepository extends ContextAware implements TaskRepositoryInterface
{

    public function getNamespace()
    {
        return 'test';
    }

    public function task($arg1, $arg2, $arg3 = 'default')
    {
        return array(
            'arg1' => $arg1,
            'arg2' => $arg2,
            'arg3' => $arg3
        );
    }

}
