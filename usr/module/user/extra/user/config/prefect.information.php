<?php
/**
 * Prefect information form config
 *
 */

return array(
    'fullname'  => array(
        'element' => Pi::api('user', 'form')->getElement('fullname'),
        'filter'  => Pi::api('user', 'form')->getFilter('fullname'),
    ),

    'gender'    => array(
        'element' => Pi::api('user', 'form')->getElement('gender'),
        'filter'  => Pi::api('user', 'form')->getFilter('gender'),
    ),

    'birthdate' => array(
        'element' => Pi::api('user', 'form')->getElement('birthdate'),
        'filter'  => Pi::api('user', 'form')->getFilter('birthdate'),
    ),

    'location'  => array(
        'element' => Pi::api('user', 'form')->getElement('location'),
        'filter'  => Pi::api('user', 'form')->getFilter('location'),
    ),
);