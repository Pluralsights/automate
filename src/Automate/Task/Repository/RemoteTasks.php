<?php

/*
 * This file is part of the Automate package.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Automate\Task\Repository;

use Automate\Command\Helper\DialogHelper;
use Automate\Context\ContextAware;
use Automate\Exception\RemoteException;
use Automate\Remote\Remote;
use Automate\Task\TaskRepositoryInterface;
use Automate\Utils\Path;
use Automate\Utils\Remote\Key;
use Symfony\Component\Finder\Finder;

/**
 * @author Julien Jacottet <jjacottet@gmail.com>
 */
class RemoteTasks extends ContextAware implements TaskRepositoryInterface
{

    /**
     * {@inheritDoc}
     */
    public function getNamespace()
    {
        return 'remote';
    }

    /**
     * Connect remote
     *
     * @param string $host
     * @param string $user
     * @param string $password
     * @param array  $groups
     * @param bool   $isMaster
     */
    public function connect($host, $user, $password = null, $groups = array(), $isMaster = false)
    {
        $dialog = new DialogHelper();

        $this->context->getOutput()->writeln(sprintf('Connect to  <comment>%s@%s (%s)</comment>', $user, $host, implode(', ', $groups)));
        if (!$password) {
            $password = $dialog->askHiddenResponse($this->context->getOutput(), sprintf('Password for %s@%s ? ', $user, $host));
        } elseif ($password instanceof Key) {
            $passPhrase = $dialog->askHiddenResponse($this->context->getOutput(), sprintf('RSA passphrase for %s@%s ? ', $user, $host));
            $password = rsa($password, $passPhrase);
        }

        $remote = new Remote($host, $user, $password, $groups, $isMaster);
        $remote->connect();
        $this->context->getRemoteManager()->add($remote);
    }

    /**
     * Change directory
     *
     * @param string      $path
     * @param string|null $group
     */
    public function cd($path, $group = null)
    {
        $remotesManager = $this->context->getRemoteManager();
        $remotes = $group ? $remotesManager->getGroup($group) : $remotesManager->getRemotes();

        foreach ($remotes as $remote) {
            $this->context->getOutput()->writeln(sprintf('<info>[%s]</info> run <comment>cd %s</comment>', $remote->getHost(), $path));
            $remote->cd($path);
        }
    }

    /**
     * Change directory for master
     *
     * @param string $path
     */
    public function cdMaster($path)
    {
        $remote = $this->context->getRemoteManager()->getMaster();
        $this->context->getOutput()->writeln(sprintf('<info>[%s]</info> run <comment>cd %s</comment>', $remote->getHost(), $path));
        $this->context->getRemoteManager()->getMaster()->cd($path);
    }

    /**
     * execute command
     *
     * @param string      $path
     * @param string|null $group
     */
    public function run($command, $group = null)
    {
        $remotesManager = $this->context->getRemoteManager();
        $remotes = $group ? $remotesManager->getGroup($group) : $remotesManager->getRemotes();

        foreach ($remotes as $remote) {
            $this->context->getOutput()->writeln(sprintf('<info>[%s]</info> run <comment>%s</comment>', $remote->getHost(), $command));
            $rs = $remote->execute($command);
            if ($rs) {
                $this->context->getOutput()->writeln($rs);
            }
        }
    }

    /**
     * execute command  (master)
     *
     * @param string $path
     */
    public function runMaster($command)
    {
        $remote = $this->context->getRemoteManager()->getMaster();

        $this->context->getOutput()->writeln(sprintf('<info>[%s]</info> run <comment>%s</comment>', $remote->getHost(), $command));
        $rs = $remote->execute($command);
        if ($rs) {
            $this->context->getOutput()->writeln($rs);
        }

        return $rs;

    }

    /**
     * Upload files
     *
     * @param string $from
     * @param string $to
     * @param array  $group
     * @param array  $excludes
     *
     * @throws RemoteException
     */
    public function upload($from, $to, $group = null, $excludes = array())
    {
        $output = $this->context->getOutput();
        $remotes = $this->context->getRemoteManager()->getGroup($group);

        $from = Path::normalize(realpath($from));

        if (is_file($from) && is_readable($from)) {
            foreach ($remotes as $remote) {
                $output->writeln(sprintf('<info>[%s]</info> Uploading file <info>%s</info> to <info>%s</info>', $remote->getHost(), $from, $to));
                $remote->uploadFile($from, $to);
            }
        } elseif (is_dir($from)) {

            $files = Path::getFilesList($from, $excludes);

            foreach ($remotes as $remote) {
                $output->writeln(sprintf('<info>[%s]</info> Uploading <info>%s</info> to <info>%s</info>', $remote->getHost(), $from, $to));

                /** @var $progress ProgressHelper */
                $progress = $this->context->getApp()->getHelperSet()->get('progress');
                $progress->start($output, $files->count());

                /** @var $file \SplFileInfo */
                foreach ($files as $file) {

                    $fromFile = Path::normalize($file->getRealPath());
                    $toFile = str_replace($from, '', $fromFile);
                    $toFile = rtrim($to, '/') . '/' . ltrim($toFile, '/');

                    $remote->uploadFile($fromFile, $toFile);
                    $progress->advance();
                }

                $progress->finish();

            }

        } else {
            throw new RemoteException("Uploading path '$from' does not exist.");
        }
    }

    public function uploadMaster($from, $to)
    {

    }

    public function download($from, $to, $host = null)
    {

    }

}
