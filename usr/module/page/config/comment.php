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
    'page' => [
        'title'    => _a('Page comments'),
        'icon'     => 'icon-post',
        'callback' => 'Module\Page\Api\Comment',
        'locator'  => 'Module\Page\Api\Comment',
    ],
];
