<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Controller\Admin;

use Pi;
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
