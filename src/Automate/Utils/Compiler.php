<?php

/*
 * This file is part of the Automate package.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Automate\Utils;

use Symfony\Component\Finder\Finder;

/**
 * Phar compiler
 *
 * @author Julien Jacottet <jjacottet@gmail.com>
 *
 */
class Compiler
{
    protected $fromDir;

    /**
     * Compile phar
     *
     * @param string $fromDir
     * @param string $pharFile
     *
     * @throws \RuntimeException
     */
    public function compile($fromDir, $pharFile = 'automate.phar')
    {
        $this->fromDir = realpath(rtrim($fromDir, DIRECTORY_SEPARATOR));

        if (!is_dir($this->fromDir)) {
            throw new \RuntimeException("Directory '$fromDir' does not exist.");
        }

        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $phar = new \Phar($pharFile, 0, $pharFile);
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->name('*.yml')
            ->name('automate')
            ->exclude('phpunit')
            ->exclude('Tests')
            ->exclude('test')
            ->exclude('.automate')
            ->notName('Compiler.php')
            ->in($this->fromDir);

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        $this->addFile($phar, new \SplFileInfo($this->fromDir . '/LICENSE'), false);

        $phar->setStub($this->getStub());

        $phar->stopBuffering();

        echo "Phar package compiled successful.\n";

        unset($phar);
    }

    /**
     * Add file in phar
     *
     * @param \Phar        $phar
     * @param \SplFileInfo $file
     * @param bool         $strip
     */
    private function addFile(\Phar $phar, \SplFileInfo $file, $strip = true)
    {
        $path = str_replace($this->fromDir . DIRECTORY_SEPARATOR, '', $file->getRealPath());

        $content = file_get_contents($file);
        if ($strip) {
            $content = $this->stripWhitespace($content);
        }

        $phar->addFromString($path, $content);
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     *
     * @param  string $source A PHP string
     * @return string The PHP string with the whitespace removed
     */
    private function stripWhitespace($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }

    private function getStub()
    {
        return <<<'EOF'
<?php
require 'phar://automate.phar/bin/automate';

__HALT_COMPILER();
EOF;
    }
}
