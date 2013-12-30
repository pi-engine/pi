<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */
namespace Module\Search\Block;

use Pi;

class Block
{
	public static function search()
	{
		$module = Pi::service('module')->current();
		$link = Pi::url(
            'search/index/dispatch'
        );
		return array(
			'module'	=> $module,
			'link'		=> $link,
		);
	}
}