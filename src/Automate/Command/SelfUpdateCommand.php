<?php

/*
 * This file is part of the Automate package.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Automate\Command;

use Automate\Exception\CommandException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This command unlock remotes servers
 *
 * @author Julien Jacottet <jjacottet@gmail.com>
 *
 */
class SelfUpdateCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('self-update')
            ->setAliases(array('selfupdate'))
            ->setDescription('Update automate.phar to the latest version.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command replace your automate.phar by the
latest version.
EOT
            )
        ;
    }
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        preg_match('/\((.*?)\)/', $this->getApplication()->getLongVersion(), $match);
        $localVersion = isset($match[1]) ? $match[1] : '';

        if (false !== $remoteVersion = @file_get_contents('https://github.com/julienj/automate/raw/master/build/version')) {
            if ($localVersion == $remoteVersion) {
                $output->writeln('<info>Automate is already up to date.</info>');

                return;
            }
        }

        $output->writeln('<info>Download ...</info>');

        $remoteFilename = 'https://github.com/julienj/automate/raw/master/build/automate.phar';
        $localFilename = $_SERVER['argv'][0];
        $tempFilename = basename($localFilename, '.phar').'-tmp.phar';
        if (false === @file_get_contents($remoteFilename)) {
            $output->writeln('<error>Unable to download new versions from the server.</error>');

            return 1;
        }

        try {
            copy($remoteFilename, $tempFilename);
            chmod($tempFilename, 0777 & ~umask());

            // test the phar validity
            $phar = new \Phar($tempFilename);
            // free the variable to unlock the file
            unset($phar);
            rename($tempFilename, $localFilename);

            $output->writeln('<info>automate updated.</info>');
        } catch (\Exception $e) {
            if (!$e instanceof \UnexpectedValueException && !$e instanceof \PharException) {
                throw $e;
            }
            unlink($tempFilename);
            $output->writeln(sprintf('<error>The download is corrupt (%s).</error>', $e->getMessage()));
            $output->writeln('<error>Please re-run the self-update command to try again.</error>');

            return 1;
        }
    }


}
