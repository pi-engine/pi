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
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    'page' => array(
        'title'     => _a('Page comments'),
        'icon'      => 'icon-post',
        'callback'  => 'Module\Page\Api\Comment',
        'locator'   => 'Module\Page\Api\Comment',
    ),
);
