<?php

/*
 * This file is part of the Automate package.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Automate\Strategy;
use Automate\Context\ContextAware;
use Automate\Context\ContextAwareInterface;

/**
 * Strategies manager
 *
 * @author Julien Jacottet <jjacottet@gmail.com>
 */
class StrategiesManager extends ContextAware
{

    /**
     * @var StrategyInterface[]
     */
    protected $strategies = array();

    /**
     * Add strategy to manager
     *
     * @param StrategyInterface $strategy
     */
    public function add(StrategyInterface $strategy)
    {
        if ($strategy instanceof ContextAwareInterface) {
            $strategy->setContext($this->context);
        }

        $this->strategies[$strategy->getName()] = $strategy;
    }

    /**
     * Get by name
     *
     * @return StrategyInterface
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new \UnexpectedValueException(sprintf('Unable to find "%s" strategy', $name));
        }

        return $this->strategies[$name];
    }

    /**
     * Has strategy
     *
     * @return boolean
     */
    public function has($name)
    {
        return array_key_exists($name, $this->strategies);
    }

    /**
     * Get all strategies
     *
     * @return StrategyInterface[]
     */
    public function getAll()
    {
        return $this->strategies;
    }

}
