<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-7-17
 * Time: 下午2:11
 */
return array(
        // Custom navigation
    'site' => array(
        'contact'     => array(
            'label'         => _a('Contact us'),
            'route'         => '.page',
            'action'        => 'contact',
        ),
     ),

    'front'   => array(
        'pag'     => array(
            'label'         => __('form'),
            'route'         => 'default',
            'controller'    => 'form',
            'action'        => 'index',
        ),
        'begin'  =>array(
            'label'        =>__('首页'),
            'route'        =>'default',
            'controller'  =>'index',
            'action'      =>'index',
        ),
    ),
    'admin'   => array(
        'pagea'     => array(
            'label'         => __('Contact'),
            'route'         => 'admin',
            'controller'    => 'index',
            'action'        => 'index',
        ),
        'pageb'     => array(
            'label'         => __('list'),
            'route'         => 'admin',
            'controller'    => 'page',
            'action'        => 'index',
        ),
    ),
);
