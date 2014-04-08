<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Comment specs
 *
 * @see Pi\Application\Installer\Resource\Comment
 * @author Zongshu Lin <lin40553024@163.com>
 */
return array(
    'article' => array(
        'title'         => _a('Article comments'),
        'icon'          => 'icon-post',
        'callback'      => 'Module\Article\Api\Comment',
        'locator'       => array(
            'controller'    => 'article',
            'action'        => 'detail',
            'identifier'    => 'id',
        ),
    ),
);
