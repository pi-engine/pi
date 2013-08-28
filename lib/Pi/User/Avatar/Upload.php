<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\User\Avatar;

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
        $src = Pi::url($this->getRelativePath($uid, $size));

        return $src;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath($uid, $size = null)
    {
        if (null === $size) {
            $path = array();
            foreach (array(
                    'mini',
                    'xsmall',
                    'medium',
                    'normal',
                    'large',
                    'xlarge',
                    'xxlarge'
                ) as $key
            ) {
                $path[$key] = Pi::Path($this->getRelativePath($uid, $key));
            }
        } else {
            $path = Pi::Path($this->getRelativePath($uid, $size));
        }

        return $path;
    }

    /**
     * Get relative path
     *
     * @param string $size
     * @return string
     */
    protected function getRelativePath($uid, $size = '')
    {
        $folder = $this->canonizeSize($size);
        $avatar = $this->model->avatar;
        $path = sprintf('upload/avatar/%s/%s', $folder, $avatar);

        return $path;
    }

    /**
     * Canonize sie
     * @param string $size
     * @return string
     */
    protected function canonizeSize($size)
    {
        switch ($size) {
            case 'mini':
            case 'xsmall':
            case 'medium':
            case 'large':
            case 'xlarge':
            case 'xxlarge':
                $folder = $size;
                break;
            case 'o':
            case 'original':
                $folder = 'original';
                break;
            case 'normal':
            default:
                $folder = 'normal';
                break;
        }

        return $folder;
    }
}
