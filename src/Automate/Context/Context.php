<?php

/*
 * This file is part of the Automate package.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Automate\Context;

use Automate\Automate;
use Automate\Exception\CommandException;
use Automate\Local\Local;
use Automate\Remote\RemotesManager;
use Automate\Strategy\StrategiesManager;
use Automate\Task\TasksManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Context
 *
 * @author Julien Jacottet <jjacottet@gmail.com>
 */
class Context
{

    /**
     * @var array
     */
    protected $config;

    /**
     * @var TasksManager
     */
    protected $tasksManager;

    /**
     * @var RemotesManager
     */
    protected $remoteManager;

    /**
     * @var StrategiesManager
     */
    protected $strategiesManager;

    /**
     * @var Local
     */
    protected $local;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Automate
     */
    protected $app;

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Check if the command is launched from a automate project
     *
     * @throws CommandException
     */
    public function checkIsAutomateProject()
    {
        if (!$this->config) {
            throw new CommandException('Automate could not find a .automate/config.yml file');
        }
    }

    /**
     * @param TasksManager $tasksManager
     */
    public function setTasksManager(TasksManager $tasksManager)
    {
        $this->tasksManager = $tasksManager;
    }

    /**
     * @return TasksManager
     */
    public function getTasksManager()
    {
        return $this->tasksManager;
    }

    /**
     * Set remote manager
     *
     * @param RemotesManager $remoteManager
     */
    public function setRemoteManager(RemotesManager $remoteManager)
    {
        $this->remoteManager = $remoteManager;
    }

    /**
     * Get remote manager
     *
     * @return RemotesManager
     */
    public function getRemoteManager()
    {
        return $this->remoteManager;
    }

    /**
     * @param StrategiesManager $strategiesManager
     */
    public function setStrategiesManager(StrategiesManager $strategiesManager)
    {
        $this->strategiesManager = $strategiesManager;
    }

    /**
     * @return StrategiesManager
     */
    public function getStrategiesManager()
    {
        return $this->strategiesManager;
    }

    /**
     * @param Local $local
     */
    public function setLocal(Local $local)
    {
        $this->local = $local;
    }

    /**
     * @return Local
     */
    public function getLocal()
    {
        return $this->local;
    }

    /**
     * @param Automate $app
     */
    public function setApp(Automate $app)
    {
        $this->app = $app;
    }

    /**
     * @return \Automate\Automate
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param InputInterface $input
     */
    public function setInput(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * @return InputInterface
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

}
