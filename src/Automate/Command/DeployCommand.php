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

/**
 * This command deploy you project on remotes servers
 *
 * @author Julien Jacottet <jjacottet@gmail.com>
 *
 */
class DeployCommand extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('deploy')
            ->setDescription('Deploy sources on remotes servers')
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
                        ->scalarNode('group')->isRequired()->end()
                        ->scalarNode('from')->defaultValue('./')->end()
                        ->scalarNode('to')->isRequired()->end()
                        ->integerNode('max_release')->defaultValue(5)->min(2)->max(50)->end()
                        ->scalarNode('symlink_dir')->defaultValue('current')->end()
                        ->scalarNode('releases_dir')->defaultValue('releases')->end()
                        ->scalarNode('shared_dir')->defaultValue('shared')->end()
                        ->scalarNode('strategy')->isRequired()->end()
                        ->arrayNode('excludes')->prototype('scalar')->end()->end()
                        ->integerNode('max')->defaultValue(5)->min(2)->max(50)->end()
                        ->scalarNode('symlink_dir')->defaultValue('current')->end()
                        ->scalarNode('releases_dir')->defaultValue('releases')->end()
                        ->scalarNode('shared_dir')->defaultValue('shared')->end()
                        ->arrayNode('shared')->prototype('scalar')->end()->end()
                        ->arrayNode('hooks')
                            ->children()
                                ->append($this->getHookConfigurationBuilder('pre_deploy'))
                                ->append($this->getHookConfigurationBuilder('on_deploy'))
                                ->append($this->getHookConfigurationBuilder('post_deploy'))
                            ->end()
                        ->end()
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

        $dialog->writeSection($output, 'Automate start deployment');

        $releaseId = uniqid();
        $output->writeln(sprintf('Release ID :<info> %s</info>', $releaseId));

        // set path
        $remoteBasePath = $confDeployment['to'];
        $releasesDir = $remoteBasePath . '/' . $confDeployment['releases_dir'];
        $releaseDir = $releasesDir . '/' . $releaseId;
        $sharedDir = $remoteBasePath . '/' . $confDeployment['shared_dir'];
        $currentDir = $remoteBasePath . '/' . $confDeployment['symlink_dir'];

        array_push($confDeployment['excludes'], '.automate');

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

        // check lock file
        $remoteManager = $this->context->getRemoteManager();
        foreach ($remoteManager->getGroup($deploymentGroup) as $remote) {
            if ($remote->execute('test -e automate.lock && echo "Found"')) {
                throw new CommandException('There are a lock, you can use unlock command');
            }
        }

        $this->runHook('pre_deploy');

        /**
         *  create lock file
         */
        $tasksManager->run('remote:run', array(
            'command' => sprintf('echo "%s" > automate.lock', $releaseId),
            'group'  => $deploymentGroup

        ));

        $dialog->writeSubSection($output, 'Prepare release');

        /*
         * Create release folder
         */
        $tasksManager->run('remote:cd', array('path' => $releasesDir, 'group' => $deploymentGroup));
        $tasksManager->run('remote:run', array(
            'command' => sprintf('mkdir -p %s', $releaseId),
            'group'  => $deploymentGroup

        ));

        try {

            $tasksManager->run('remote:cd', array('path' => $releaseDir, 'group' => $deploymentGroup));

            /*
             * Deploy
             */
            $dialog->writeSubSection($output, 'Deploy');
            $strategy = $this->context->getStrategiesManager()->get($confDeployment['strategy']);
            $strategy->deploy($releaseId, $confDeployment);

            /*
              * Create symlinks
              */
            $dialog->writeSubSection($output, 'Create symlinks for shards folders');
            foreach ($confDeployment['shared'] as $shared) {
                $src = $sharedDir . '/' . $shared;
                $symlink = $releaseDir . '/' . $shared;
                $command = sprintf('ln -s %s %s',$src, $symlink);
                $tasksManager->run('remote:run', array('command' => $command, 'group'  => $deploymentGroup));
            }

            $this->runHook('on_deploy');

            /*
             * publish release
             */
            $dialog->writeSubSection($output, 'Publish release');
            $tasksManager->run('remote:run', array('command' => sprintf('rm -rf %s', $currentDir), 'group'  => $deploymentGroup));
            $tasksManager->run('remote:run', array('command' => sprintf('ln -s %s %s', $releaseDir, $currentDir), 'group'  => $deploymentGroup));

        } catch (\Exception $e) {
            $dialog->writeError($output, $e->getMessage());
            $tasksManager->run('remote:run', array('command' => sprintf('rm -rf %s', $releaseDir), 'group'  => $deploymentGroup));
            $this->deleteLockFile($remoteBasePath, $deploymentGroup, $releaseId);
            throw $e;
        }

        $this->runHook('post_deploy');
        $this->deleteLockFile($remoteBasePath, $deploymentGroup, $releaseId);
        $this->deleteOldReleases($releaseId, $releasesDir, $deploymentGroup, $confDeployment['max_release']);

        $dialog->writeSuccess($output, 'Deployment is complete');

    }

    /**
     * run hook tasks
     *
     * @param string $name
     */
    protected function runHook($name)
    {
        $dialog = $this->getDialogHelper();

        $dialog->writeSubSection($this->context->getOutput(), sprintf('Run %s tasks', $name));

        $conf = $this->getConfiguration();
        $tasks = $conf['deployment']['hooks'][$name];

        foreach ($tasks as $task) {
            $this->context->getOutput()->writeln(sprintf('Run %s task', $task['name']));
            $this->context->getTasksManager()->run($task['name'], $task['params']);
        }
    }

    protected function deleteLockFile($remoteBasePath, $deploymentGroup, $releaseId)
    {
        $tasksManager = $this->context->getTasksManager();

        $tasksManager->run('remote:cd', array('path' => $remoteBasePath, 'group' => $deploymentGroup));
        $tasksManager->run('remote:run', array(
            'command' => sprintf('rm -f automate.lock', $releaseId),
            'group'  => $deploymentGroup

        ));
    }

    /**
     * Delete old releases
     *
     * @param $releaseId
     * @param $releasesDir
     * @param $deploymentGroup
     * @param $max
     */
    protected function deleteOldReleases($releaseId, $releasesDir, $deploymentGroup, $max)
    {
        $tasksManager = $this->context->getTasksManager();
        $this->getDialogHelper()->writeSubSection($this->context->getOutput(), 'Clear old releases');

        $tasksManager->run('remote:cd', array('path' => $releasesDir, 'group' => $deploymentGroup));

        $releasesList = trim($tasksManager->run('remote:runMaster', array('command' => 'ls -1')));
        $releasesList = explode("\n", $releasesList);
        $releasesList = array_reverse($releasesList);

        var_dump($releasesList);

        $releasesList = array_slice($releasesList, 0, $max);

        if (!in_array($releaseId, $releasesList)) {
            $releasesList[] = $releaseId;
        }

        foreach ($this->context->getRemoteManager()->getGroup($deploymentGroup) as $remote) {

            $releases = trim($remote->execute('ls -1'));
            $releases = explode("\n", $releases);
            foreach ($releases as $release) {
                if (!in_array($release, $releasesList)) {
                    $this->context->getOutput()->writeln(sprintf('<info>[%s]</info> Delete <comment>%s</comment> release', $remote->getHost(), $release));
                    $remote->execute('rm -rf ' . $release);
                }
            }

        }
    }
}
