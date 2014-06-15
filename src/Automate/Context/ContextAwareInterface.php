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
 * ContainerAwareInterface should be implemented by classes that depends on a Context.
 *
 * @author Julien Jacottet <jjacottet@gmail.com>
 *
 */
interface ContextAwareInterface
{
    /**
     * Sets the Context.
     *
     * @param Context|null $context A Context instance or null
     *
     */
    public function setContext(Context $context = null);
}
