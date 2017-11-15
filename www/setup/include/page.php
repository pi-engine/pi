<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

$pages = array(
    'presetting'     => array(
        'title' => _s('Presettings'),
        'desc'  => _s('Presettings and server configuration detection')
    ),
    'directive'     => array(
        'title' => _s('Basic'),
        'desc'  => _s('Basic settings for website')
    ),
    'database'      => array(
        'title' => _s('Database'),
        'desc'  => _s('Database settings')
    ),
    'admin'         => array(
        'title' => _s('Administrator'),
        'desc'  => _s('System and administrator account creation')
    ),
    'finish'        => array(
        'title' => _s('Finish'),
        'desc'  => _s('Finishing installation process'),
        'hide'  => true,
    ),
);

return $pages;
