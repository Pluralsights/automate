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

/**
 * Strategy interface
 *
 * @author Julien Jacottet <jjacottet@gmail.com>
 *
 */
interface StrategyInterface
{
    /**
     * Get strategy name
     *
     * @return string
     */
    public function getName();

    /**
     * Deploy sources on remote(s)
     *
     * @param string $releaseId
     * @param array  $options
     */
    public function deploy($releaseId, $options);
}
