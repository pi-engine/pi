<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Event/listener specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    // Event list
    'event'    => array(
        // event name (unique)
        'post_submit' => array(
            // title
            'title' => __('Post submitted'),
        ),
        'post_publish'  => array(
            'title' => __('Post published'),
        ),
        'post_update'  => array(
            'title' => __('Post updated'),
        ),
        'post_enable'  => array(
            'title' => __('Post enabled'),
        ),
        'post_disable'  => array(
            'title' => __('Post disabled'),
        ),
        'post_delete'  => array(
            'title' => __('Post deleted'),
        ),
    ),
    // Listener list
    'listener' => array(
        array(
            // event info: module, event name
            'event'     => array('comment', 'post_submit'),
            // listener callback: class, method
            'callback'  => array('event', 'postsubmit'),
        ),
        array(
            'event'     => array('comment', 'post_publish'),
            'callback'  => array('event', 'postpublish'),
        ),
        array(
            'event'     => array('comment', 'post_enable'),
            'callback'  => array('event', 'postenable'),
        ),
        array(
            'event'     => array('comment', 'post_update'),
            'callback'  => array('event', 'postupdate'),
        ),
        array(
            'event'     => array('comment', 'post_disable'),
            'callback'  => array('event', 'postdisable'),
        ),
        array(
            'event'     => array('comment', 'post_delete'),
            'callback'  => array('event', 'postdelete'),
        ),
    ),
);
