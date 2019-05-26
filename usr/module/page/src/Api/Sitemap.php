<?php

namespace Module\Page\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

class Sitemap extends AbstractApi
{
    public function sitemap()
    {
        if (!Pi::service('module')->isActive('sitemap')) {
            return;   
        }
        
         // Remove old links
        Pi::api('sitemap', 'sitemap')->removeAll('page', 'page');

        // find and import
        $columns = array('id', 'active', 'slug');
        
        $select = Pi::model('page', 'page')->select()->columns($columns);
        $rowset = Pi::model('page', 'page')->selectWith($select);

        foreach ($rowset as $row) {
            if ($row->active) {
                $loc = Pi::url(Pi::service("url")->assemble("page", array('controller' => 'page', 'slug' => $row->slug)));
                Pi::api('sitemap', 'sitemap')->singleLink($loc, 1, 'page', 'page', $row->id);
            }
            
        }
    }
}