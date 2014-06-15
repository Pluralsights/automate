<?php

/*
 * This file is part of the Automate package.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Automate\Remote;

/**
 * Remote manager
 *
 * @author Julien Jacottet <jjacottet@gmail.com>
 */
class RemotesManager
{

    /**
     * @var Remote[]
     */
    protected $remotes = array();

    /**
     * Add remote to manager
     *
     * @param Remote $remote
     */
    public function add(Remote $remote)
    {
        if ($remote->getIsMaster() and $this->hasMaster()) {
            throw new \UnexpectedValueException('You can have only one master');
        }

        $this->remotes[] = $remote;
    }

    /**
     * Get remotes
     *
     * @param array $groups
     */
    public function getRemotes()
    {
        return $this->remotes;
    }

    /**
     * Get servers in a group
     *
     * @param string $name
     *
     * @return Remote[]
     */
    public function getGroup($name)
    {
        return  array_filter($this->remotes, function (Remote $remote) use ($name) {
            return in_array($name, $remote->getGroups());
        });
    }

    /**
     * Group exist ?
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasGroup($name)
    {
        return $this->getGroup($name) ? true : false;
    }

    /**
     * Get Master
     *
     * @return Remote|null
     */
    public function getMaster()
    {
        $master = array_filter($this->remotes, function (Remote $remote) {
            return $remote->getIsMaster();
        });

        return $master ? reset($master) : null;
    }

    /**
     * Has master
     *
     * @return boolean
     */
    public function hasMaster()
    {
        return $this->getMaster() ? true : false;
    }

}
