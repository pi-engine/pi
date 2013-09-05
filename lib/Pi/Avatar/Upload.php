<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Avatar;

use Pi;

/**
 * Uploaded avatar handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Upload extends AbstractAvatar
{
    /**
     * {@inheritDoc}
     */
    public function getSource($uid, $size = '')
    {
        $src = '';
        if (!$uid) {
            return $src;
        }

        if ($uid == $this->user->get('id')) {
            $avatar = $this->user->get('avatar');
        } else {
            $avatar = Pi::user()->get($uid, 'avatar');
        }
        if ($avatar && false === strpos($avatar, '@')) {
            $src = $this->build($avatar, $size, $uid);
        }

        return $src;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceList($uids, $size = '')
    {
        $result = array();
        $avatars = Pi::user()->get($uids, 'avatar');
        foreach ($avatars as $uid => $avatar) {
            if ($avatar && false === strpos($avatar, '@')) {
                $result[$uid] = $this->build($avatar, $size, $uid);
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function build($source, $size = '', $uid = null)
    {
        return $this->buildPath($source, $size, $uid, true);
    }

    /**
     * Build avatar path/URL
     *
     * @param string    $source
     * @param string    $size
     * @param int       $uid
     * @param bool      $toUrl
     *
     * @return string
     */
    public function buildPath(
        $source,
        $size = '',
        $uid = null,
        $toUrl = false
    ) {
        if ($toUrl) {
            if (isset($this->options['root_url'])) {
                $root = $this->options['root_url'];
            } else {
                $root = Pi::url('upload/avatar', true);
            }
        } else {
            if (isset($this->options['root_path'])) {
                $root = $this->options['root_path'];
            } else {
                $root = Pi::path('upload/avatar');
            }
        }
        if (!empty($this->options['path'])) {
            $pattern = $this->options['path'];
        } else {
            $pattern = '%size%/%uid%_%source%';
        }
        $size = $this->canonizeSize($size, false);
        if (is_callable($pattern)) {
            $path = call_user_func($pattern, array(
                'source'    => $source,
                'size'      => $size,
                'uid'       => $uid
            ));
        } else {
            $path = str_replace(
                array('source', 'size', 'uid'),
                array($source, $size, $uid),
                $pattern
            );
        }
        $src = $root . '/' . $path;

        return $src;
    }

    /**
     * Get/Create avatar meta (path, src and size) of a user
     *
     * - Get meta of a specific size
     * ```
     *  // Get meta of existent avatar
     *  $meta = Pi::service('avatar')->upload->getMeta(123, 'hashed', 'small');

     *  // Create meta
     *  $meta = Pi::service('avatar')->upload->getMeta(123, '', 'small');
     *
     *  // Output:
     *  $result = array('path' => <path-to-avatar>, 'size' => <int>);
     * ```
     *
     * - Get meta of full size list
     * ```
     *  // Get meta of existent avatars
     *  $meta = Pi::service('avatar')->upload->getMeta(123, 'hashed');

     *  // Create meta of avatars
     *  $meta = Pi::service('avatar')->upload->getMeta(123);
     *
     *  // Output:
     *  $result = array(
     *      'mini'  => array('path' => <path-to-avatar>, 'size' => <int>),
     *      'small' => array('path' => <path-to-avatar>, 'size' => <int>),
     *      <...>,
     *  );
     * ```
     *
     * @param int       $uid    User id
     * @param string    $source
     *      Filename; A hased filename without extension will be generated
     *      if it is not specified
     * @param string    $size
     *
     * @return array|bool
     */
    public function getMeta($uid, $source = '', $size = '')
    {
        if (!$uid) {
            return false;
        }

        $source = $this->hashSource($uid, $source);
        $_this = $this;
        $getMeta = function ($size) use ($_this, $source, $uid) {
            $meta = array(
                'src'   => $_this->build($source, $size, $uid),
                'path'  => $_this->buildPath($source, $size, $uid),
                'size'  => $_this->canonizeSize($size)
            );

            return $meta;
        };

        if ($size) {
            $result = $getMeta($size);
        } else {
            $result = array();
            $sizeList = Pi::service('avatar')->getSize();
            foreach (array_keys($sizeList) as $name) {
                $result[$name] = $getMeta($name);
            }
        }

        return $result;
    }

    /**
     * Generate hashed source name
     *
     * @param int    $uid
     * @param string $source
     * @param string $extension
     *
     * @return mixed|string
     */
    public function hashSource($uid, $source, $extension = '')
    {
        if (!$extension) {
            if ($source) {
                $extension = pathinfo($source, PATHINFO_EXTENSION);
            } else {
                $extension = 'jpg';
            }
        }
        if (!empty($this->options['source_hash'])) {
            $result = call_user_func($this->options['source_hash'], array(
                'uid'       => $uid,
                'extension' => $extension,
                'source'    => $source,
            ));
        } else {
            $result = md5(uniqid($uid)) .  '.' . $extension;
        }

        return $result;
    }
}
