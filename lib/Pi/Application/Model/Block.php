<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
