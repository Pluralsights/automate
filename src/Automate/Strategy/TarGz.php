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
use Automate\Utils\Path;

/**
 * TarGz strategy
 *
 * Deploy sources from local with ftp
 *
 * @author Julien Jacottet <jjacottet@gmail.com>
 *
 */
class TarGz extends ContextAware implements StrategyInterface
{

    /**
     * {@inheritDoc}
     */
    public function deploy($releaseId, $conf)
    {
        $to = $conf['to'] . '/' . $conf['releases_dir'] . '/' . $releaseId;
        $files = Path::getFilesList($conf['from'], $conf['excludes']);

        $tar = new \PharData('.automate/release.tar');
        foreach($files as $file) {
            $basePath = realpath($conf['from']);
            $tar->addFile(ltrim($file->getRealPath(), $basePath));
        }
        $tar->compress(\Phar::GZ);

        $tasksManager = $this->context->getTasksManager();

        $tasksManager->run('remote:upload', array(
            'from' => './.automate/release.tar.gz',
            'to' => $to . '/release.tar.gz',
            'group' => $conf['group'],
        ));

        $tasksManager->run('remote:cd', array('path' => $to, 'group' => $conf['group']));

        $tasksManager->run('remote:run', array(
            'command' => ' tar zxf release.tar.gz',
            'group' => $conf['group'],
        ));

        @unlink('.automate/release.tar');
        @unlink('.automate/release.tar.gz');

    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'targz';
    }

}
