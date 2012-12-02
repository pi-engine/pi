<?php
/**
 * Pi Engine Markitup Editor Renderer
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
 * @package         Editor\Markitup
 * @version         $Id$
 */

namespace Editor\Markitup;

use Pi;
use Pi\Editor\AbstractRenderer;
//use Zend\View\Renderer\RendererInterface as Renderer;
use Zend\Form\ElementInterface;

class Renderer extends AbstractRenderer
{
    protected $configFile = 'editor.markitup.php';

    /**
     * Renders editor contents
     *
     * @param  ElementInterface $element
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $options = array_merge($this->getOptions(), $element->getOptions());
        $attributes = array_merge($this->getAttributes(), $element->getAttributes());
        $view = $this->view;

        // Set up set and skin
        $set = isset($options['set']) ? $options['set'] : 'html';
        $skin = isset($options['skin']) ? $options['skin'] : 'simple';

        // Set up CSS
        $view->css(sprintf('%s/editor/markitup/skins/%s/style.css', Pi::url('script'), $skin));
        $view->css(sprintf('%s/editor/markitup/sets/%s/style.css', Pi::url('script'), $set));
        // Set up JavaScript
        $view->jQuery();
        $view->js(Pi::url('script') . '/editor/markitup/jquery.markitup.js');
        $view->js(sprintf('%s/editor/markitup/sets/%s/set.js', Pi::url('script'), $set));

        $parserpath = '';
        if (!empty($options['sets'][$set]['parser_path'])) {
            $parserpath = $options['sets'][$set]['parser_path'];
        } else {
            $path = sprintf('%s/editor/markitup/sets/%s/preview.php', Pi::path('script'), $set);
            if (file_exists($path)) {
                $parserpath = sprintf('~/sets/%s/preview.php', $set);
            }
        }
        $scriptJs =<<<"EOT"
$(document).ready(function()	{
    mySettings.previewParserPath = '{$parserpath}';
    mySettings.previewParserVar = 'preview';
    $('#%s').markItUp(mySettings);
});
EOT;
        if (isset($attributes['id'])) {
            $id = $attributes['id'];
        } else {
            $id = uniqid($element->getName());
        }
        $element->setAttribute('id', $id);
        $js = sprintf($scriptJs, $id);
        $view->HeadScript('script', $js);
    }
}
