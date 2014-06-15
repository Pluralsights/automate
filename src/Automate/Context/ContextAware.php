<?php

/*
 * This file is part of the Automate package.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Automate\Context;

/**
 * A simple implementation of ContextAwareInterface.
 *
 * @author Julien Jacottet <jjacottet@gmail.com>
 *
 */
abstract class ContextAware implements ContextAwareInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * {@inheritDoc}
     */
    public function setContext(Context $context = null)
    {
        $this->context = $context;
    }

}
