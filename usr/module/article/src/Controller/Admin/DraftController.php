<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Controller\Admin;

use Module\Article\Controller\Front\DraftController as FrontDraft;

/**
 * Draft controller extend from front DraftController
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class DraftController extends FrontDraft
{
    /**
     * Section identifier
     * @var string
     */
    protected $section = 'admin';
}
