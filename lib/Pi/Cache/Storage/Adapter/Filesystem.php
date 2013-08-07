<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Cache\Storage\Adapter;

use Zend\Cache\Storage\Adapter\Filesystem as ZendFilesystem;
use Exception as BaseException;
use Zend\Cache\Storage\Adapter\Exception;

/**
 * Filesystem cache adapter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Filesystem extends ZendFilesystem
{
    /**
     * {@inheritdoc}
     */
    protected function internalGetItem(
        &$normalizedKey,
        &$success = null,
        &$casToken = null
    ) {
        /**#@+
         * Skip extra file reading cost
         */
        /*
        if (!$this->internalHasItem($normalizedKey)) {
            $success = false;
            return null;
        }
        */
        /**#@-*/

        try {
            $filespec = $this->getFileSpec($normalizedKey);
            /**#@+
             * Internal file content parsing
             */
            //$data     = $this->getFileContent($filespec . '.dat');
            $data     = $this->getFileData($filespec . '.dat');
            /**#@-*/

            // use filemtime + filesize as CAS token
            if (func_num_args() > 2) {
                $casToken = filemtime($filespec . '.dat')
                          . filesize($filespec . '.dat');
            }
            $success  = (null === $data) ? false : true;
            return $data;

        } catch (BaseException $e) {
            $success = false;
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function internalGetItems(array & $normalizedKeys)
    {
        $options = $this->getOptions();
        // Don't change argument passed by reference
        $keys    = $normalizedKeys;
        $result  = array();
        while ($keys) {

            // LOCK_NB if more than one items have to read
            $nonBlocking = count($keys) > 1;
            $wouldblock  = null;

            // read items
            foreach ($keys as $i => $key) {
                /**#@+
                 * Skip extra file reading cost
                 */
                /*
                if (!$this->internalHasItem($key)) {
                    unset($keys[$i]);
                    continue;
                }
                */
                /**#@-*/

                $filespec = $this->getFileSpec($key);
                /**#@+
                 * Internal file content parsing
                 */
                //$data     = $this->getFileContent($filespec . '.dat',
                //$nonBlocking, $wouldblock);
                $data     = $this->getFileData(
                    $filespec . '.dat',
                    $nonBlocking, $wouldblock
                );
                /**#-*/
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
     * {@inheritdoc}
     */
    protected function internalHasItem(& $normalizedKey)
    {
        $file = $this->getFileSpec($normalizedKey) . '.dat';
        /**#@+
         * Internale content parsing
         */
        if (!$this->fileValid($file)) {
            return false;
        }
        /**#@-*/

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function internalSetItem(& $normalizedKey, & $value)
    {
        $filespec = $this->getFileSpec($normalizedKey);

        $this->prepareDirectoryStructure($filespec);

        // write data in non-blocking mode
        $wouldblock = null;
        /**#@+
         * Internale content assemble
         */
        //$this->putFileContent($filespec . '.dat', $value, true, $wouldblock);
        $this->putFileData($filespec . '.dat', $value, true, $wouldblock);
        /**#@-*/

        // delete related tag file (if present)
        $this->unlink($filespec . '.tag');

        // Retry writing data in blocking mode if it was blocked before
        if ($wouldblock) {
            /**#@+
             * Internale content assemble
             */
            //$this->putFileContent($filespec . '.dat', $value);
            $this->putFileData($filespec . '.dat', $value);
            /**#@-*/
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function internalSetItems(array & $normalizedKeyValuePairs)
    {
        $oldUmask    = null;

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
                /**#@+
                 * Internale content assemble
                 */
                //$this->putFileContent($file, $content, $nonBlocking,
                //$wouldblock);
                $this->putFileData($file, $content, $nonBlocking, $wouldblock);
                /**#@-*/
                if (!$nonBlocking || !$wouldblock) {
                    unset($contents[$file]);
                }
            }
        }

        // return OK
        return array();
    }

    /**
     * Read and validate cache data, return valid content
     *
     * @param  string  $file        File complete path
     * @param  bool $nonBlocking Don't block script if file is locked
     * @param  bool $wouldblock  The optional argument is set to TRUE
     *      if the lock would block
     * @return string|null
     */
    protected function getFileData(
        $file,
        $nonBlocking = false,
        &$wouldblock = null
    ) {
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
     * Assemble content data and write to a cache file
     *
     * @param  string  $file        File complete path
     * @param  string  $data        Data to write
     * @param  bool $nonBlocking Don't block script if file is locked
     * @param  bool $wouldblock  The optional argument is set to TRUE
     *      if the lock would block
     * @return void
     */
    protected function putFileData(
        $file,
        $data,
        $nonBlocking = false,
        &$wouldblock = null
    ) {
        $expire = 0;
        $options = $this->getOptions();
        if ($options->ttl) {
            $expire = time() + $options->ttl;
        }
        $data = $expire . ':' . $data;
        $this->putFileContent($file, $data, $nonBlocking, $wouldblock);
    }

    /**
     * Check if cached file content valid
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
}
