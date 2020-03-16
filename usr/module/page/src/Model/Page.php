<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
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
    protected $columns
        = [
            'id',
            'title',
            'name',
            'user',
            'time_created',
            'time_updated',
            'active',
            'content',
            'markup',
            'slug',
            'clicks',
            'seo_title',
            'seo_keywords',
            'seo_description',
            'main_image',
            'additional_images',
            'nav_order',
            'theme',
            'layout',
            'template',
        ];
}
