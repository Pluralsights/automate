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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command unlock remotes servers
 *
 * @author Julien Jacottet <jjacottet@gmail.com>
 *
 */
class UnlockCommand extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('unlock')
            ->setDescription('Unlock remotes hosts')
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('root');

        $rootNode
            ->ignoreExtraKeys()
            ->children()
                ->append($this->getRemotesConfigurationBuilder())
                ->arrayNode('deployment')
                ->ignoreExtraKeys()
                    ->children()
                        ->scalarNode('group')->end()
                        ->scalarNode('to')->isRequired()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $context = $this->context;

        $context->checkIsAutomateProject();

        $dialog = $this->getDialogHelper();
        $tasksManager = $context->getTasksManager();

        $conf = $this->getConfiguration();
        $confDeployment = $conf['deployment'];
        $deploymentGroup = $confDeployment['group'];

        $dialog->writeSection($output, 'Unlock remotes hosts');

        // set path
        $remoteBasePath = $confDeployment['to'];

        /*
         * ssh connection
         */
        $dialog->writeSubSection($output, 'SSH connection');
        foreach ($conf['remotes'] as $remote) {
            $tasksManager->run('remote:connect', array(
                'user' =>     $remote['user'],
                'host' =>     $remote['host'],
                'password' => $remote['password'],
                'groups' =>   $remote['groups'],
                'isMaster' => $remote['master'],
            ));
        }

        $tasksManager->run('remote:cd', array('path' => $remoteBasePath, 'group' => $deploymentGroup));

        // delete lock file
        $remoteManager = $this->context->getRemoteManager();
        foreach ($remoteManager->getGroup($deploymentGroup) as $remote) {
            if (!$remote->execute('test -e automate.lock && echo "Found"')) {
                $output->writeln(sprintf('<info>[%s]</info> No lock found', $remote->getHost()));
            } else {
                $output->writeln(sprintf('<info>[%s]</info> Delete lock file', $remote->getHost()));
                $remote->execute('rm -f automate.lock');
            }
        }

        $dialog->writeSuccess($output, 'All is OK');

    }
}
