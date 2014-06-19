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
 * Path utils
 *
 * @package Gaufrette
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 * @author  Julien Jacottet <jjacottet@gmail.com>
 */
class Path
{

    /**
     * Get iterator form dir
     * @param string $dir
     * @param array  $excludes
     *
     * @return Finder
     */
    public static function getFilesList($dir, $excludes = array())
    {

        $ignore = array_map(function ($pattern) {
            $pattern = preg_quote($pattern, '#');
            $pattern = str_replace('\*', '(.*?)', $pattern);
            $pattern = "#$pattern#";

            return $pattern;
        }, $excludes);

        $finder = new Finder();
        $files = $finder
            ->files()
            ->ignoreUnreadableDirs()
            ->ignoreVCS(true)
            ->ignoreDotFiles(false)
            ->filter(function (\SplFileInfo $file) use ($ignore) {
                foreach ($ignore as $pattern) {
                    if (preg_match($pattern, $file->getRealPath())) {
                        return false;
                    }
                }

                return true;
            })
            ->in($dir)
        ;

        return $files;
    }

    /**
     * Normalizes the given path
     *
     * @param string $path
     *
     * @return string
     */
    public static function normalize($path)
    {
        $path   = str_replace('\\', '/', $path);
        $prefix = static::getAbsolutePrefix($path);
        $path   = substr($path, strlen($prefix));
        $parts  = array_filter(explode('/', $path), 'strlen');
        $tokens = array();

        foreach ($parts as $part) {
            switch ($part) {
                case '.':
                    continue;
                case '..':
                    if (0 !== count($tokens)) {
                        array_pop($tokens);
                        continue;
                    } elseif (!empty($prefix)) {
                        continue;
                    }
                default:
                    $tokens[] = $part;
            }
        }

        return $prefix . implode('/', $tokens);
    }

    /**
     * Indicates whether the given path is absolute or not
     *
     * @param string $path A normalized path
     *
     * @return boolean
     */
    public static function isAbsolute($path)
    {
        return '' !== static::getAbsolutePrefix($path);
    }

    /**
     * Returns the absolute prefix of the given path
     *
     * @param string $path A normalized path
     *
     * @return string
     */
    public static function getAbsolutePrefix($path)
    {
        preg_match('|^(?P<prefix>([a-zA-Z]+:)?//?)|', $path, $matches);

        if (empty($matches['prefix'])) {
            return '';
        }

        return strtolower($matches['prefix']);
    }
}
