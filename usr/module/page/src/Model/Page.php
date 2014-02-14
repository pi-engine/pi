<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Page\Model;

use Pi\Application\Model\Model;

/**
 * Model class for Page
 * 
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Page extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $columns = array(
        'id', 'name', 'title', 'slug', 'content', 'markup', 'active',
        'user', 'time_created', 'seo_title', 'seo_keywords', 'seo_description'
    );
}
