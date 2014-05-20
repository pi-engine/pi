<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */
namespace Module\Tag\Api;

use Pi;
use Pi\Search\AbstractSearch;

class Search extends AbstractSearch
{
    /**
     * {@inheritDoc}
     */
    protected $table = 'tag';
    
    /**
     * {@inheritDoc}
     */
    protected $searchIn = array(
        'id',
        'term'
    );

    /**
     * {@inheritDoc}
     */
    protected $meta = array(
        'id'            => 'id',
        'term'          => 'title',
    );

    /**
     * {@inheritDoc}
     */
    protected function buildUrl(array $item)
    {
        $url = Pi::service('url')->assemble(
            'default',
            array(
                'module'        => 'tag',
                'controller'    => 'index',
                'action'        => 'list',
                'tag'           => $item['term']
        ));
        
        return $url;
    }
}