<?php

/*
 * This file is part of the Automate package.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Automate\Command;

use Automate\Exception\CommandException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This command unlock remotes servers
 *
 * @author Julien Jacottet <jjacottet@gmail.com>
 *
 */
class InitCommand extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Create configuration file')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();

        $fs = new Filesystem();

        if ($fs->exists('.automate/config.yml')) {
            throw new CommandException('.automate/config.yml already exist');
        }

        $fs->mkdir('.automate');

        $fs->copy(dirname(__DIR__) . '/Resources/skeleton/config.yml', '.automate/config.yml');

        $dialog->writeSuccess($output, '.automate/config.yml was created');
    }

}
