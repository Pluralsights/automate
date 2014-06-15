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

/**
 * FTP strategy
 *
 * Deploy sources from local with ftp
 *
 * @author Julien Jacottet <jjacottet@gmail.com>
 *
 */
class Ftp extends ContextAware implements StrategyInterface
{

    /**
     * {@inheritDoc}
     */
    public function deploy($releaseId, $conf)
    {
        $to = $conf['to'] . '/' . $conf['releases_dir'] . '/' . $releaseId;

        $this->context->getTasksManager()->run('remote:upload', array(
            'from' => $conf['from'],
            'to' => $to,
            'group' => $conf['group'],
            'excludes' => $conf['excludes'],
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'ftp';
    }

}
