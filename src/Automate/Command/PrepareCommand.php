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
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This command prepare folders on remotes servers
 *
 * @author Julien Jacottet <jjacottet@gmail.com>
 *
 */
class PrepareCommand extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('prepare')
            ->setDescription('prepare remotes')
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
                        ->scalarNode('releases_dir')->defaultValue('releases')->end()
                        ->scalarNode('shared_dir')->defaultValue('shared')->end()
                        ->arrayNode('shared')->prototype('scalar')->end()->end()
                        ->scalarNode('group')->isRequired()->end()
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

        $dialog->writeSection($output, 'Prepare remotes');


        // set path
        $remoteBasePath = $confDeployment['to'];
        $releasesDir = $remoteBasePath . '/' . $confDeployment['releases_dir'];
        $sharedDir = $remoteBasePath . '/' . $confDeployment['shared_dir'];

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

        $this->mkdir($releasesDir, $deploymentGroup);
        $this->mkdir($sharedDir, $deploymentGroup);

        foreach($confDeployment['shared'] as $shared) {
            $this->mkdir($sharedDir . '/' . $shared, $deploymentGroup);
        }

        $dialog->writeSuccess($output, 'All is OK');
    }

    protected function mkdir($path, $group)
    {
        $this->context->getTasksManager()->run('remote:run', array(
            'command' => sprintf('test ! -e %s && mkdir %s', $path, $path),
            'group'   => $group
        ));
    }

}
