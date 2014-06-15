<?php

/*
 * This file is part of the Automate package.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Automate\Task;
use Automate\Context\ContextAware;
use Automate\Context\ContextAwareInterface;

/**
 * TasksManager
 *
 * @author Julien Jacottet <jjacottet@gmail.com>
 */
class TasksManager extends ContextAware
{
    /**
     * @var TaskRepositoryInterface[]
     */
    protected $repositories = array();

    /**
     * Add repository
     *
     * @param TaskRepositoryInterface $repository
     */
    public function addRepository(TaskRepositoryInterface $repository)
    {
        if ($repository instanceof ContextAwareInterface) {
            $repository->setContext($this->context);
        }

        $this->repositories[$repository->getNamespace()] = $repository;
    }

    /**
     * Get repository
     *
     * @param $namespace
     *
     * @return TaskRepositoryInterface
     * @throws \UnexpectedValueException
     */
    public function getRepository($namespace)
    {
        if (!$this->hasRepository($namespace)) {
            throw new \UnexpectedValueException(sprintf('Unable to find "%s" repository', $namespace));
        }

        return $this->repositories[$namespace];
    }

    /**
     * Has repository
     *
     * @param $namespace
     *
     * @return boolean
     */
    public function hasRepository($namespace)
    {
        return isset($this->repositories[$namespace]);
    }

    /**
     * Run task
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return mixed
     * @throws \UnexpectedValueException
     */
    public function run($name, $parameters = array())
    {
        if (!$this->isValidTaskName($name)) {
            throw new \UnexpectedValueException(sprintf('"%s" is not a valid task name', $name));
        }

        list($namespace, $task) = explode(':', $name, 2);

        $repository = $this->getRepository($namespace);

        try {
            $reflection = new \ReflectionMethod($repository, $task);
        } catch (\Exception $e) {
            throw new \UnexpectedValueException(sprintf('Unable to find "%s" task', $name));
        }

        $args = array();
        foreach ($reflection->getParameters() as $param) {
            if (array_key_exists($param->getName(), $parameters)) {
                $args[] = $parameters[$param->getName()];
                unset($parameters[$param->getName()]);
            } elseif ($param->isOptional()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new \UnexpectedValueException(sprintf('Parameter "%s" is missing to run task "%s".', $param->getName(), $name));
            }
        }

        if ($parameters) {
            throw new \UnexpectedValueException(sprintf('Extra parameter(s) "%s" to run task "%s".', implode(', ', array_keys($parameters)), $name));
        }

        return $reflection->invokeArgs($repository, $args);
    }

    /**
     * @param $name
     *
     * @return bool
     */
    protected function isValidTaskName($name)
    {
        return (bool) preg_match('/^\w+\:\w+$/', $name);
    }

}
