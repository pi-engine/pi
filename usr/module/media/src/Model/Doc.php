<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Media\Model;

use Pi;
use Pi\Application\Model\Model;

/**
 * Model class for Doc
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Doc extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns = array(
        'id', 'url', 'path', 'filename', 'attributes',
        'title', 'description',
        'active', 'time_created', 'time_updated', 'time_deleted',
        'appkey', 'module', 'type', 'token',
        'uid', 'ip', 'count'
    );

    /**
     * {@inheritDoc}
     */
    protected $encodeColumns = array(
        'attributes'     => true,
    );
}
