<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * System navigation specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    'meta' => array(
        // Front-end navigation template
        'front'     => array(
            //'name'      => 'front',
            'section'   => 'front',
            'title'    => _t('Front navigation'),
        ),
        // Back-end navigation template
        'admin'     => array(
            //'name'      => 'admin',
            'section'   => 'admin',
            'title'     => _t('Admin navigation'),
        ),
        // Managed components
        'component' => array(
            //'name'      => 'component',
            'section'   => 'admin',
            'title'     => _t('Managed components'),
        ),
    ),
    'item' => array(
        // Front navigation items
        'front' => include __DIR__ . '/nav.front.php',
        // Admin navigation items
        'admin' => include __DIR__ . '/nav.admin.php',
        // Managed component items
        'component' => include __DIR__ . '/nav.component.php',
    )
);
