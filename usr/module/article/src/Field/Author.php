<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Field;

use Pi;

/**
 * Author element handler
 *
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Author extends CustomCommonHandler
{
    /**
     * {@inheritDoc}
     */
    public function resolve($value, $options = array())
    {
        $result = array();
        
        if ($value) {
            $author = Pi::api('api', $this->module)->getAuthorList(array(
                'id' => $value,
            ));

            if ($author) {
                $result = array_shift($author);
                if (empty($result['photo'])) {
                    $result['photo'] = Pi::service('asset')->getModuleAsset(
                        Pi::config('default_author_photo', $this->module), 
                        $this->module
                    );
                } else {
                    $result['photo'] = Pi::url($result['photo']);
                }
            }
        }
        
        return $result;
    }
}
