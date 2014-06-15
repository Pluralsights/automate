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

use Automate\Task\TasksManager;
use Automate\Tests\Fixtures\Task\TestTaskRepository;

class TasksManagerTest extends \PHPUnit_Framework_TestCase
{

    public function testAddRepository()
    {
        $tasksManager = new TasksManager();

        $repository = $this->getMock('Automate\Task\TaskRepositoryInterface');
        $repository->expects($this->any())
            ->method('getNamespace')
            ->will($this->returnValue('test'))
        ;

        $tasksManager->addRepository($repository);

        $this->assertTrue($tasksManager->hasRepository('test'));

    }

    public function testGetRepository()
    {
        $tasksManager = new TasksManager();

        $repository = $this->getMock('Automate\Task\TaskRepositoryInterface');
        $repository->expects($this->any())
            ->method('getNamespace')
            ->will($this->returnValue('test'))
        ;

        $tasksManager->addRepository($repository);

        $this->assertSame($repository, $tasksManager->getRepository('test'));
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testGetMissingRepository()
    {
        $tasksManager = new TasksManager();
        $tasksManager->getRepository('invalid');

    }

    public function testIsValidName()
    {

        $class = new \ReflectionClass('Automate\Task\TasksManager');
        $isValidTaskName = $class->getMethod('isValidTaskName');
        $isValidTaskName->setAccessible(true);

        $tasksManager = new TasksManager();

        $this->assertTrue($isValidTaskName->invokeArgs($tasksManager, array('repo:task')));
        $this->assertTrue($isValidTaskName->invokeArgs($tasksManager, array('repo:task1')));
        $this->assertTrue($isValidTaskName->invokeArgs($tasksManager, array('repo:task_1')));

        $this->assertFalse($isValidTaskName->invokeArgs($tasksManager, array('repo:task-1')));
        $this->assertFalse($isValidTaskName->invokeArgs($tasksManager, array('repo:task 1')));
        $this->assertFalse($isValidTaskName->invokeArgs($tasksManager, array('repo:task,1')));
        $this->assertFalse($isValidTaskName->invokeArgs($tasksManager, array('r epo:task1')));

    }

    public function testRunTask()
    {
        $tasksManager = new TasksManager();
        $tasksManager->addRepository(new TestTaskRepository());

        $params = array(
            'arg1' => 1,
            'arg2' => 2,
            'arg3' => 3
        );

        $result = $tasksManager->run('test:task', $params);

        $this->assertEquals($params, $result);
    }

    public function testRunTaskDefaultParam()
    {
        $tasksManager = new TasksManager();
        $tasksManager->addRepository(new TestTaskRepository());

        $params = array(
            'arg1' => 1,
            'arg2' => 2,
            'arg3' => 'default'
        );

        $result = $tasksManager->run('test:task', array_slice($params, 0, 2));

        $this->assertEquals($params, $result);
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testRunTaskMissingParam()
    {
        $tasksManager = new TasksManager();
        $tasksManager->addRepository(new TestTaskRepository());

        $params = array(
            'arg1' => 1,
        );

        $tasksManager->run('test:task', $params);
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testRunTaskExtraParam()
    {
        $tasksManager = new TasksManager();
        $tasksManager->addRepository(new TestTaskRepository());

        $params = array(
            'arg1' => 1,
            'arg2' => 2,
            'extra' => 1,
        );

        $tasksManager->run('test:task', $params);
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testRunInvalidTask()
    {
        $tasksManager = new TasksManager();
        $tasksManager->addRepository(new TestTaskRepository());

        $tasksManager->run('test:invalid', array());
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testRunInvalidNameTask()
    {
        $tasksManager = new TasksManager();
        $tasksManager->addRepository(new TestTaskRepository());

        $tasksManager->run('test invalid', array());
    }
}
