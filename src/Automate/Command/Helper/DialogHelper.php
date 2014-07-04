<?php

/*
 * This file is part of the Automate package.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Automate\Command\Helper;

use Symfony\Component\Console\Helper\DialogHelper as BaseDialogHelper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dialog Helper
 *
 */
class DialogHelper extends BaseDialogHelper
{

    /**
     * Write section
     *
     * @param OutputInterface $output
     * @param string          $text
     */
    public function writeSection(OutputInterface $output, $text)
    {
        $output->writeln(array(
            '',
            $this->getHelperSet()->get('formatter')->formatBlock($text, 'bg=blue;fg=white', true),
            '',
        ));
    }

    /**
     * Write sub section
     *
     * @param OutputInterface $output
     * @param string          $text
     */
    public function writeSubSection(OutputInterface $output, $text)
    {
        $output->writeln(array(
            '',
            $this->getHelperSet()->get('formatter')->formatBlock($text, 'bg=blue;fg=white'),
            '',
        ));
    }

    /**
     * Write success
     *
     * @param OutputInterface $output
     * @param string          $text
     */
    public function writeSuccess(OutputInterface $output, $text)
    {
        $output->writeln(array(
            '',
            $this->getHelperSet()->get('formatter')->formatBlock($text, 'bg=green;fg=white'),
            '',
        ));
    }

    /**
     * Write error
     *
     * @param OutputInterface $output
     * @param string          $text
     */
    public function writeError(OutputInterface $output, $text)
    {
        $output->writeln(array(
            '',
            $this->getHelperSet()->get('formatter')->formatBlock($text, 'bg=red;fg=white'),
            '',
        ));
    }

    /**
     * Write cmd return
     *
     * @param OutputInterface $output
     * @param string          $text
     */
    public function writeReturn(OutputInterface $output, $text)
    {
        $output->writeln(array(
            '',
            $this->getHelperSet()->get('formatter')->formatBlock($text, 'bg=cyan;fg=black'),
            '',
        ));
    }

}
