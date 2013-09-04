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
 * Selective avatar handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Select extends AbstractAvatar
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
        if ($avatar && preg_match('/[a-z0-9\-]/i', $avatar)) {
            $src = $this->build($avatar, $size);
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
            if ($avatar && preg_match('/[a-z0-9\-]/i', $avatar)) {
                $result[$uid] = $this->build($avatar, $size);
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function build($source, $size = '', $uid = null)
    {
        if (isset($this->options['root_url'])) {
            $root = $this->options['root_url'];
        } else {
            $root = Pi::url('static/avatar', true);
        }
        if (!empty($this->options['path'])) {
            $pattern = $this->options['path'];
        } else {
            $extension = isset($this->options['extension'])
                ? $this->options['extension'] : 'jpg';
            $pattern = '%source%/%size%.' . $extension;
        }
        $size = $this->canonizeSize($size, false);
        if (is_callable($pattern)) {
            $path = call_user_func($pattern, array(
                'source'    => $source,
                'size'      => $size
            ));
        } else {
            $path = str_replace(
                array('source', 'size'),
                array($source, $size),
                $pattern
            );
        }
        $src = $root . '/' . $path;

        return $src;
    }

    /**
     * Get/Create avatar list meta (identifier and corresponding URL)
     *
     *  // Output:
     *  $result = array(
     *      <identifier>  => <avatar-url>,
     *      <...>,
     *  );
     * ```
     *
     * @param string    $size
     *
     * @return array|bool
     */
    public function getMeta($size = 'normal')
    {
        if (isset($this->options['root_path'])) {
            $root = $this->options['root_path'];
        } else {
            $root = Pi::path('static/avatar');
        }
        $result = array();
        $iterator = new \DirectoryIterator($root);
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }
            $directory = $fileinfo->getFilename();
            if (!preg_match('/[a-z0-9\-]/i', $directory)) {
                continue;
            }
            $result[$directory] = $this->build($directory, $size);
        }

        return $result;
    }
}
