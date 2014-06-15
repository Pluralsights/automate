<?php

/*
 * This file is part of the Automate package.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Automate\Task;

/**
 * TaskRepositoryInterface should be implemented by repositories.
 *
 * @author Julien Jacottet <jjacottet@gmail.com>
 *
 */
interface TaskRepositoryInterface
{
    /**
     * Get repository namespace
     *
     * @return string
     */
    public function getNamespace();
}
