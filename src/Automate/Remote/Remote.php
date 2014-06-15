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

use Automate\Exception\RemoteException;
use Automate\Utils\Remote\Key;

/**
 * Import from https://github.com/elfet/deployer
 *
 * @author Anton Medvedev <anton@elfet.ru>
 */
class Remote
{
    /**
     * Host name
     *
     * @var string
     */
    protected $host;

    /**
     * SSH User
     *
     * @var string
     */
    protected $user;

    /**
     * SSH password
     *
     * @var string
     */
    protected $password;

    /**
     * Groups
     *
     * @var array
     */
    protected $groups;

    /**
     * is master
     *
     * @var boolean
     */
    protected $isMaster;

    /**
     * is connected
     *
     * @var boolean
     */
    protected $isConnected;

    /**
     * current path
     *
     * @var string
     */
    protected $cd = null;

    /***
     * SFTP connexion
     *
     * @var \Net_SFTP
     */
    protected $sftp;

    /**
     * cache for uploadFile function
     *
     * @var array
     */
    private $directories = array();

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     * @param array  $groups
     * @param bool   $isMaster
     */
    public function __construct($host, $user, $password, $groups = array(), $isMaster = false)
    {
        $this->host     = $host;
        $this->user     = $user;
        $this->password = $password;
        $this->groups   = (array) $groups;
        $this->isMaster = $isMaster;
        $this->isConnected = false;
    }

    /**
     * Get groups
     *
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Get Hosts
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get user
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get is master
     *
     * @return boolean
     */
    public function getIsMaster()
    {
        return $this->isMaster;
    }

    /**
     * Get is connected
     *
     * @return boolean
     */
    public function getIsConnected()
    {
        return $this->isConnected;
    }

    /**
     * Connect remote server
     *
     * @throws RemoteException
     */
    public function connect()
    {
        if (!$this->isConnected) {

            $this->sftp = $this->getSFTP();

            if ($this->password instanceof Key) {
                $this->password = $this->password->key();
            }

            if (!$this->sftp->login($this->user, $this->password)) {
                throw new RemoteException(sprintf('Can not login to server "%s".', $this->host));
            }

            $this->isConnected = true;
        }
    }

    /**
     * Init SFTP
     */
    public  function getSFTP()
    {
        return new \Net_SFTP($this->host);
    }

    /**
     * Change directory
     *
     * @param string $directory
     */
    public function cd($directory)
    {
        $this->cd = $directory;
    }

    /**
     * Execute command
     *
     * @param string $command
     *
     * @return String
     * @throws RemoteException
     */
    public function execute($command)
    {
        $this->connect();

        if (null !== $this->cd) {
            $command = "cd $this->cd && $command";
        }

        $result = $this->sftp->exec($command);

        if ($this->sftp->getStdError()) {
            throw new RemoteException($this->sftp->getStdError());
        }

        return $result;
    }

    /**
     * upload file to remote
     *
     * @param type $from
     * @param type $to
     */
    public function uploadFile($from, $to)
    {
        $this->connect();

        $dir = dirname($to);
        if (!isset($this->directories[$dir])) {
            $this->sftp->mkdir($dir, -1, true);
            $this->directories[$dir] = true;
        }

        if (!$this->sftp->put($to, $from, NET_SFTP_LOCAL_FILE)) {
            throw new RemoteException(implode($this->sftp->getSFTPErrors(), "\n"));
        }
    }

    /**
     * download file from remote in current directory
     *
     * @param type $from
     * @param type $to
     */
    public function downloadFile($from, $to)
    {
        $this->connect();

        return $this->sftp->get($from, $to);
    }

}
