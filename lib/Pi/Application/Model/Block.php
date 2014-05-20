<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Model;

/**
 * Block model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Block extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns = array(
        'id',
        'root', 'name', 'title', 'description',
        'module', 'template', 'render',
        'config', 'type', 'content',
        'cache_ttl', 'cache_level', 'title_hidden', 'body_fullsize',
        'active', 'cloned', 'class', 'subline'
    );

    /**
     * {@inheritDoc}
     */
    protected $encodeColumns = array(
        'config'    => true,
    );
}
