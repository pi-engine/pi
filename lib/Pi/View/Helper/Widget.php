<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         View
 */

namespace   Pi\View\Helper;

use Pi\Db\RowGateway\RowGateway as BlockModel;

/**
 * Helper for fetching and rendering a widget
 *
 * Usage inside a pthml template
 *
 * ```
 *  $this->widget(
 *      'block-name',
 *      array('title_hidden' => 1, 'opt1' => 'val1', 'opt2' => 'val2',
 *            'cache_ttl' => 300, 'cache_level' => 'role')
 *  );
 *  $this->widget(
 *      'block-name',
 *      array('link' => '/link/to/a/URL', 'opt1' => 'val1', 'opt2' => 'val2')
 *  );
 *  $this->widget(
 *      'block-name',
 *      array('style' => 'specified-css-class', 'opt1' => 'val1',
 *            'opt2' => 'val2')
 *  );
 *  $this->widget(24, array('opt1' => 'val1', 'opt2' => 'val2'));
 *  $this->widget()->load(24);
 *  $this->widget()->render($blockModel);
 * ```
 *
 * @see Pi\Application\Registry\Block
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
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
        $content = (is_array($data) && isset($data['content']))
            ? $data['content'] : false;
        return $content;
    }
}
