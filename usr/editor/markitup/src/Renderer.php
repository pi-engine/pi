<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Editor\Markitup;

use Pi;
use Pi\Editor\AbstractRenderer;
use Zend\Form\ElementInterface;

/**
 * Markitup renderer
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
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
        $attributes = array_merge(
            $this->getAttributes(),
            $element->getAttributes()
        );
        $view = $this->view;

        // Set up set and skin
        $set = isset($options['set']) ? $options['set'] : 'html';
        $skin = isset($options['skin']) ? $options['skin'] : 'simple';

        // Set up CSS
        $view->css(sprintf('%s/editor/markitup/skins/%s/style.css',
                           Pi::url('script'), $skin));
        $view->css(sprintf('%s/editor/markitup/sets/%s/style.css',
                           Pi::url('script'), $set));
        // Set up JavaScript
        $view->jQuery();
        $view->js(Pi::url('script') . '/editor/markitup/jquery.markitup.js');
        $view->js(sprintf('%s/editor/markitup/sets/%s/set.js',
                          Pi::url('script'), $set));

        $parserpath = '';
        if (!empty($options['sets'][$set]['parser_path'])) {
            $parserpath = $options['sets'][$set]['parser_path'];
        } else {
            $path = sprintf('%s/editor/markitup/sets/%s/preview.php',
                            Pi::path('script'), $set);
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
