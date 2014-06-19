<?php

/*
 * This file is part of the Automate package.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Automate;

use Automate\Command\DeployCommand;
use Automate\Command\InitCommand;
use Automate\Command\PrepareCommand;
use Automate\Command\SelfUpdateCommand;
use Automate\Command\UnlockCommand;
use Automate\Context\Context;
use Automate\Context\ContextAwareInterface;
use Automate\Local\Local;
use Automate\Remote\RemotesManager;
use Automate\Strategy\Ftp;
use Automate\Strategy\StrategiesManager;
use Automate\Strategy\TarGz;
use Automate\Task\Repository\LocalTasks;
use Automate\Task\Repository\RemoteTasks;
use Automate\Task\TasksManager;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Yaml;

/**
 * Automate Application
 *
 * @author Julien Jacottet <jjacottet@gmail.com>
 */
class Automate extends Application
{
    const VERSION = '0.0.1-DEV';

    private static $logo = "
     _         _                        _
    / \  _   _| |_ ___  _ __ ___   __ _| |_ ___
   / _ \| | | | __/ _ \| '_ ` _ \ / _` | __/ _ \
  / ___ \ |_| | || (_) | | | | | | (_| | ||  __/
 /_/   \_\__,_|\__\___/|_| |_| |_|\__,_|\__\___|

";

    /**
     * Automate Context
     *
     * @var Context
     */
    protected $context;

    /**
     * Contructor
     */
    public function __construct()
    {
        $this->context = new Context();
        $this->context->setLocal(new Local());
        $this->context->setConfig($this->getConfiguration());
        $this->context->setRemoteManager(new RemotesManager());
        $this->context->setTasksManager($this->getTasksManager());
        $this->context->setStrategiesManager($this->getStrategiesManager());
        $this->context->setApp($this);

        $version = static::VERSION;

        if ('@'.'git-commit@' !== $commit = '@git-commit@') {
            $version .= ' ('.substr($commit, 0, 7).')';
        }

        parent::__construct('Automate', $version);
    }

    /**
     * create task manager
     */
    public function getTasksManager()
    {
        $manager = new TasksManager();
        $manager->setContext($this->context);
        $manager->addRepository(new RemoteTasks());
        $manager->addRepository(new LocalTasks());

        return $manager;
    }

    /**
     * create stategies manager
     */
    public function getStrategiesManager()
    {
        $manager = new StrategiesManager();
        $manager->setContext($this->context);
        $manager->add(new Ftp());
        $manager->add(new TarGz());

        return $manager;
    }

    /**
     * Load configuration
     */
    protected function getConfiguration()
    {
        if (file_exists('.automate/config.yml')) {
            return Yaml::parse('.automate/config.yml');
        }

        return null;
    }

    /**
     * Initializes all commands
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new DeployCommand();
        $commands[] = new UnlockCommand();
        $commands[] = new InitCommand();
        $commands[] = new SelfUpdateCommand();
        $commands[] = new PrepareCommand();
        return $commands;
    }

    /**
     * {@inheritDoc}
     */
    public function add(Command $command)
    {
        if ($command instanceof ContextAwareInterface) {
            $command->setContext($this->context);
        }

        parent::add($command);
    }

    /**
     * {@inheritDoc}
     */
    public function getHelp()
    {
        return self::$logo . parent::getHelp();
    }

}
