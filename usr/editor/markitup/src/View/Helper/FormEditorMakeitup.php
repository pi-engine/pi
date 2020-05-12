<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Editor\Ckeditor\View\Helper;

use Pi;
use Pi\Form\View\Helper\AbstractEditor;
use Laminas\Form\ElementInterface;

/**
 * Editor element helper
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FormEditorMakeitup extends AbstractEditor
{
    /** @var string */
    protected $configFile = 'editor.markitup.php';

    /**
     * {@inheritDoc}
     */
    public function render(ElementInterface $element)
    {
        $options = $element->getOptions();
        $view    = $this->view;

        // Set up set and skin
        $set  = isset($options['set']) ? $options['set'] : 'html';
        $skin = isset($options['skin']) ? $options['skin'] : 'simple';

        // Set up CSS
        $view->css(sprintf(
            '%s/editor/markitup/skins/%s/style.css',
            Pi::url('script'),
            $skin
        ));
        $view->css(sprintf(
            '%s/editor/markitup/sets/%s/style.css',
            Pi::url('script'),
            $set
        ));
        // Set up JavaScript
        $view->jQuery();
        $view->js(Pi::url('script') . '/editor/markitup/jquery.markitup.js');
        $view->js(sprintf(
            '%s/editor/markitup/sets/%s/set.js',
            Pi::url('script'),
            $set
        ));

        $parserpath = '';
        if (!empty($options['sets'][$set]['parser_path'])) {
            $parserpath = $options['sets'][$set]['parser_path'];
        } else {
            $path = sprintf(
                '%s/editor/markitup/sets/%s/preview.php',
                Pi::path('script'),
                $set
            );
            if (file_exists($path)) {
                $parserpath = sprintf('~/sets/%s/preview.php', $set);
            }
        }
        $scriptJs
            = <<<EOT
$(document).ready(function()    {
    mySettings.previewParserPath = '{$parserpath}';
    mySettings.previewParserVar = 'preview';
    $('#%s').markItUp(mySettings);
});
EOT;
        if (!empty($attributes['id'])) {
            $id = $attributes['id'];
        } else {
            $id = uniqid($element->getName() . '_');
        }
        $element->setAttribute('id', $id);
        $js = sprintf($scriptJs, $id);
        $view->HeadScript('script', $js);
    }
}
