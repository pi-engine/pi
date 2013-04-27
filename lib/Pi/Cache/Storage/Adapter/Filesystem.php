<?php
/**
 * Filesystem cache adapter
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Pi\Cache
 * @version         $Id$
 */

namespace Pi\Cache\Storage\Adapter;

use Zend\Cache\Storage\Adapter\Filesystem as ZendFilesystem;
use Exception as BaseException;
use Zend\Cache\Storage\Adapter\Exception;

class Filesystem extends ZendFilesystem
{
    /**
     * Internal method to get an item.
     *
     * @param  string  $normalizedKey
     * @param  bool $success
     * @param  mixed   $casToken
     * @return mixed Data on success, null on failure
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetItem(& $normalizedKey, & $success = null, & $casToken = null)
    {
        /*
        if (!$this->internalHasItem($normalizedKey)) {
            $success = false;
            return null;
        }
        */

        try {
            $filespec = $this->getFileSpec($normalizedKey);
            $data     = $this->getFileData($filespec . '.dat');

            // use filemtime + filesize as CAS token
            if (func_num_args() > 2) {
                $casToken = filemtime($filespec . '.dat') . filesize($filespec . '.dat');
            }
            $success  = (null === $data) ? false : true;
            return $data;

        } catch (BaseException $e) {
            $success = false;
            throw $e;
        }
    }

    /**
     * Internal method to get multiple items.
     *
     * @param  array $normalizedKeys
     * @return array Associative array of keys and values
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetItems(array & $normalizedKeys)
    {
        //$options = $this->getOptions();
        $keys    = $normalizedKeys; // Don't change argument passed by reference
        $result  = array();
        while ($keys) {

            // LOCK_NB if more than one items have to read
            $nonBlocking = count($keys) > 1;
            $wouldblock  = null;

            // read items
            foreach ($keys as $i => $key) {
                /*
                if (!$this->internalHasItem($key)) {
                    unset($keys[$i]);
                    continue;
                }
                */

                $filespec = $this->getFileSpec($key);
                $data     = $this->getFileData($filespec . '.dat', $nonBlocking, $wouldblock);
                if ($nonBlocking && $wouldblock) {
                    continue;
                } else {
                    unset($keys[$i]);
                }

                $result[$key] = $data;
            }

            // TODO: Don't check ttl after first iteration
            // $options['ttl'] = 0;
        }

        return $result;
    }

    /**
     * Internal method to test if an item exists.
     *
     * @param  string $normalizedKey
     * @return bool
     */
    protected function internalHasItem(& $normalizedKey)
    {
        $file = $this->getFileSpec($normalizedKey) . '.dat';
        if (!$this->fileValid($file)) {
            return false;
        }

        return true;
    }

    /**
     * Internal method to store an item.
     *
     * @param  string $normalizedKey
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalSetItem(& $normalizedKey, & $value)
    {
        $filespec = $this->getFileSpec($normalizedKey);

        $this->prepareDirectoryStructure($filespec);

        // write data in non-blocking mode
        $wouldblock = null;
        $this->putFileData($filespec . '.dat', $value, true, $wouldblock);

        // delete related tag file (if present)
        $this->unlink($filespec . '.tag');

        // Retry writing data in blocking mode if it was blocked before
        if ($wouldblock) {
            $this->putFileData($filespec . '.dat', $value);
        }

        return true;
    }

    /**
     * Internal method to store multiple items.
     *
     * @param  array $normalizedKeyValuePairs
     * @return array Array of not stored keys
     * @throws Exception\ExceptionInterface
     */
    protected function internalSetItems(array & $normalizedKeyValuePairs)
    {
        //$oldUmask    = null;

        // create an associated array of files and contents to write
        $contents = array();
        foreach ($normalizedKeyValuePairs as $key => & $value) {
            $filespec = $this->getFileSpec($key);
            $this->prepareDirectoryStructure($filespec);

            // *.dat file
            $contents[$filespec . '.dat'] = & $value;

            // *.tag file
            $this->unlink($filespec . '.tag');
        }

        // write to disk
        while ($contents) {
            $nonBlocking = count($contents) > 1;
            $wouldblock  = null;

            foreach ($contents as $file => & $content) {
                $this->putFileData($file, $content, $nonBlocking, $wouldblock);
                if (!$nonBlocking || !$wouldblock) {
                    unset($contents[$file]);
                }
            }
        }

        // return OK
        return array();
    }

    /**
     * Read valid cache data
     *
     * @param  string  $file        File complete path
     * @param  bool $nonBlocking Don't block script if file is locked
     * @param  bool $wouldblock  The optional argument is set to TRUE if the lock would block
     * @return string|null
     */
    protected function getFileData($file, $nonBlocking = false, & $wouldblock = null)
    {
        $result = null;
        if (file_exists($file)) {
            $content = $this->getFileContent($file, $nonBlocking, $wouldblock);
            list($expire, $data) = explode(':', $content, 2);
            if (empty($expire) || time() < $expire) {
                $result = $data;
            }
        }

        return $result;
    }

    /**
     * Check if cache file valid
     *
     * @param  string  $file        File complete path
     * @return bool
     */
    protected function fileValid($file)
    {
        if (!file_exists($file)) {
            return false;
        }

        $content = $this->getFileContent($file);
        $pos = strpos($content, ':');
        if (false === $pos) {
            return false;
        }
        $expire = (int) substr($content, 0, $pos);
        if ($expire && time() >= $expire) {
            return false;
        }

        return true;
    }

    /**
     * Write cache data to a file
     *
     * @param  string  $file        File complete path
     * @param  string  $data        Data to write
     * @param  bool $nonBlocking Don't block script if file is locked
     * @param  bool $wouldblock  The optional argument is set to TRUE if the lock would block
     * @return void
     */
    protected function putFileData($file, $data, $nonBlocking = false, & $wouldblock = null)
    {
        $expire = 0;
        $options = $this->getOptions();
        if ($options->ttl) {
            $expire = time() + $options->ttl;
        }
        $data = $expire . ':' . $data;
        $this->putFileContent($file, $data, $nonBlocking, $wouldblock);
    }
}