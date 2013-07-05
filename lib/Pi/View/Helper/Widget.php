<?php
/**
 * Widget helper
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Pi\View
 * @subpackage      Helper
 * @version         $Id$
 */

namespace   Pi\View\Helper;
use Pi\Db\RowGateway\RowGateway as BlockModel;


/**
 * Helper for fetching and rendering a widget
 * @see Pi\Application\Registry\Block
 *
 * Usage inside a pthml template:
 * <code>
 *  $this->widget('block-name', array('title_hidden' => 1, 'opt1' => 'val1', 'opt2' => 'val2', 'cache_ttl' => 300, 'cache_level' => 'role'));
 *  $this->widget('block-name', array('link' => '/link/to/a/URL', 'opt1' => 'val1', 'opt2' => 'val2'));
 *  $this->widget('block-name', array('style' => 'specified-css-class', 'opt1' => 'val1', 'opt2' => 'val2'));
 *  $this->widget(24, array('opt1' => 'val1', 'opt2' => 'val2'));
 *  $this->widget()->load(24);
 *  $this->widget()->render($blockModel);
 * </code>
 */
class Widget extends Block
{
    /**
     * Render a block model
     *
     * @param   BlockModel $block
     * @param   array $options
     * @return  string|false
     */
    public function render(BlockModel $block, $options = array())
    {
        $data = parent::render($block, $options);
        $content = (is_array($data) && isset($data['content'])) ? $data['content'] : false;
        return $content;
    }
}
