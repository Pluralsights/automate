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
use Automate\Strategy\StrategiesManager;

class StrategiesManagerTest extends \PHPUnit_Framework_TestCase
{

    public function testAddStrategy()
    {
        $strategiesManager = new StrategiesManager();

        $strategy = $this->getMock('Automate\Strategy\StrategyInterface');
        $strategy->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('test'))
        ;

        $strategiesManager->add($strategy);

        $this->assertTrue($strategiesManager->has('test'));
        $this->assertFalse($strategiesManager->has('invalid'));

        $this->assertInstanceOf('Automate\Strategy\StrategyInterface', $strategiesManager->get('test'));

    }

    public function testAddStrategyImplementContextAware()
    {
        $context = new Context();
        $strategiesManager = new StrategiesManager();
        $strategiesManager->setContext($context);

        $strategy = new Ftp();

        $strategiesManager->add($strategy);

        $contextAttr = \PHPUnit_Framework_Assert::readAttribute($strategiesManager->get('ftp'), 'context');

        $this->assertInstanceOf('Automate\Context\Context', $contextAttr);

    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testGetInvalidStrategy()
    {
        $strategiesManager = new StrategiesManager();

        $strategiesManager->get('test');
    }

    public function testAddMultipleStrategies()
    {
        $strategiesManager = new StrategiesManager();

        $strategy = $this->getMock('Automate\Strategy\StrategyInterface');
        $strategy->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('test'))
        ;

        $strategy2 = $this->getMock('Automate\Strategy\StrategyInterface');
        $strategy2->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('test2'))
        ;

        $strategiesManager->add($strategy);
        $strategiesManager->add($strategy2);

        $this->assertEquals(2, count($strategiesManager->getAll()));

        foreach ($strategiesManager->getAll() as $strategy) {
            $this->assertInstanceOf('Automate\Strategy\StrategyInterface', $strategy);
        }
    }
}
