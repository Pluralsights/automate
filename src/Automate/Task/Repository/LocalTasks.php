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

use Automate\Context\ContextAware;
use Automate\Task\TaskRepositoryInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Julien Jacottet <jjacottet@gmail.com>
 */
class LocalTasks extends ContextAware implements TaskRepositoryInterface
{

    public function getNamespace()
    {
        return 'local';
    }

    /**
     * Run command
     *
     * @param string          $command
     * @param OutputInterface $output
     */
    public function run($command)
    {
        $this->context->getOutput()->writeln(sprintf('Run local commande : %s', $command));
        $rs = $this->context->getLocal()->execute($command);
        if ($rs) {
            $this->context->getOutput()->writeln($rs);
        }

    }

}
