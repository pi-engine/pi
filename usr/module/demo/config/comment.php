<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Comment specs
 *
 * @see Pi\Application\Installer\Resource\Comment
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return [
    'article' => [
        'title'   => _a('Demo comments'),
        'icon'    => 'icon-post',
        //'callback'  => 'Module\Demo\Comment\Article',
        'locator' => [
            'controller' => 'article',
            'action'     => 'index',
            'identifier' => 'id',
            'params'     => [
                // <param>      => <value>
            ],
        ],
    ],
];
