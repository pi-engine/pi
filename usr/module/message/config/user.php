<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * User profile and resource specs
 *
 * @see Pi\Application\Installer\Resource\User
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    // Quicklinks
    'quicklink' => array(
        'message'    => array(
            'title' => __('Messages'),
            'link'  => Pi::service('url')->assemble('default', array('module' => 'message')),
            'icon'  => 'icon-bell',
        ),
    ),
);
