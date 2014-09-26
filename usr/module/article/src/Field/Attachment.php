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
 * Attachment handler
 *
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Attachment extends Gallery
{
    /**
     * Asset type
     * @var string 
     */
    protected $type = 'attachment';
    
    /**
     * {@inheritDoc}
     */
    public function resolve($value, $options = array())
    {
        $result = array();
        
        $mediaIds = array_filter(explode(',', $value));
        if (empty($mediaIds)) {
            return array();
        }
        
        $rowset = Pi::model('media', $this->module)->select(
            array('id' => $mediaIds)
        );
        foreach ($rowset as $row) {
            $result[$row->id] = array(
                'original_name' => $row->title,
                'extension'     => $row->type,
                'size'          => $row->size,
                'url'           => Pi::service('url')->assemble('default', array(
                    'module'       => $this->module,
                    'controller'   => 'media',
                    'action'       => 'download',
                    'name'         => $row->id,
                )),
            );
        }
        
        return $result;
    }
}
