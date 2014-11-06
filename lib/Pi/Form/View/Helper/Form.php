<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\View\Helper;

use Zend\Form\View\Helper\Form as FormHelper;
use Zend\Form\FormInterface;


/**
 * View helper for form rendering w/o bootstrap style
 *
 * Styles:
 * - single: Single column or full width
 * - multiple: Multiple columns
 * - popup: For popup windows
 * - others: Zend raw style
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Form extends FormHelper
{
    /**
     * {@inheritDoc}
     * @param array|string|false $options
     */
    public function __invoke(FormInterface $form = null, $options = array())
    {
        if (!$form) {
            return $this;
        }

        return $this->render($form, $options);
    }

    /**
     * {@inheritDoc}
     * @param array|string|false $options Options for rendering:
     *                                    `style`   - horizontal, vertical, inline, popup, raw
     *                                    `column`  - single, multiple
     */
    public function render(FormInterface $form, $options = array())
    {
        // Canonize options
        if (!is_array($options)) {
            if (is_string($options)) {
                $options = array('column' => $options);
            } elseif (!$options) {
                $options = array('column' => '');
            }
        }
        if (!isset($options['style'])) {
            $options['style'] = '';
        }
        switch ($options['style']) {
            case 'vertical':
                $style = 'vertical';
                $class = '';
                break;
            case 'inline':
                $style = 'inline';
                $class = 'form-inline';
                break;
            case 'popup':
                $style = 'popup';
                $class = 'form-horizontal';
                break;
            case 'horizontal':
            case '':
                $style = 'horizontal';
                $class = 'form-horizontal';
                break;
            default:
                $style = '';
                $class = '';
                break;
        }

        // Render Zend Form directly if style is not desired
        if (!$style) {
            return parent::render($form);
        }

        $column = isset($options['column']) ? $options['column'] : '';

        $attributes = array();
        $formClass = array($class);
        $formClass[] = $form->getAttribute('class');
        $class = implode(' ', array_filter($formClass));
        if ($class) {
            $attributes['class'] = $class;
        }

        if ('popup' == $style) {
            $this->view->jQuery();
            $this->view->bootstrap('js/bootstrap.min.js');

            if (!empty($options['label'])) {
                $form->setLabel($options['label']);
            }

            $id = !empty($options['id'])
                ? $options['id']
                : ($form->getAttribute('id') ?: 'popup-form');
            $attributes['id'] = $id;
        }

        $form->setAttributes($attributes);
        if (method_exists($form, 'prepare')) {
            $form->prepare();
        }

        $elements   = $form->elementList();
        $groups     = $form->getGroups();
        $emptyControl = array('checkbox', 'multi_checkbox', 'radio', 'file');
        $markRequired =<<<EOT
        <i class="text-danger" style="margin-right: 5px;">*</i>
EOT;
        $this->view->FormElementErrors()
            ->setMessageOpenFormat('')
            ->setMessageCloseString('');

        // Anonymous functions for element/group rendering
        $renderElement = function ($element) use (
            $markRequired,
            $emptyControl,
            $style,
            $column
        ) {
            $type = $element->getAttribute('type') ? : 'text';
            $isEmptyControl = in_array($type, $emptyControl);

            if (!$isEmptyControl) {
                $class      = $element->getAttribute('class');
                $attrClass  = 'form-control' . ($class ? ' ' . $class : '');
                $element->setAttribute('class', $attrClass);
            }

            $renderPattern =<<<EOT
<div class="form-group%error_class%" data-name="%element_name%">
    %label_html%
    %element_html%
</div>
EOT;
            $labelPattern =<<<EOT
<label class="%label_size% control-label">%mark_required%%label_content%</label>
EOT;
            $descPattern =<<<EOT
<div class="text-muted">%desc_content%</div>
EOT;

            switch ($type) {
                case 'checkbox':
                    $elementPattern =<<<EOT
<div class="%element_size% js-form-element">
    <div class="checkbox">
        <label>
            %element_content%%desc_html%
        </label>
    </div>
</div>
<div class="%error_size% help-block">%error_content%</div>
EOT;
                    break;
                case 'multi_checkbox':
                    $elementPattern =<<<EOT
<div class="%element_size% js-form-element">
    <div class="checkbox">
        %element_content%%desc_html%
    </div>
</div>
<div class="%error_size% help-block">%error_content%</div>
EOT;
                    break;
                case 'radio':
                    $elementPattern =<<<EOT
<div class="%element_size% js-form-element">
    <div class="radio">
        %element_content%%desc_html%
    </div>
</div>
<div class="%error_size% help-block">%error_content%</div>
EOT;
                    break;
                default:
                    $elementPattern =<<<EOT
<div class="%element_size% js-form-element">
    %element_content%%desc_html%
</div>
<div class="%error_size% help-block">%error_content%</div>
EOT;
                    break;
            }

            $vars = array();

            switch ($style) {
                case 'popup':
                    $vars['label_size'] = 'col-md-3';
                    $vars['element_size'] = 'col-sm-8';
                    $vars['error_size'] = 'col-sm-8';
                    break;

                case 'inline':
                    $labelPattern =<<<EOT
<label class="sr-only">
    %label_content%
</label>
EOT;
                    $elementPattern =<<<EOT
    %element_content%
EOT;
                    break;

                case 'vertical':
                    $vars['label_size'] = '';
                    $vars['element_size'] = '';
                    $vars['error_size'] = '';
                    break;

                case 'horizontal':
                default:
                    if ('multiple' == $column) {
                        $vars['label_size'] = 'col-md-2';
                        $vars['element_size'] = 'col-md-4';
                        $vars['error_size'] = 'col-md-4';
                    } else {
                        $vars['label_size'] = 'col-sm-3 col-lg-2';
                        $vars['element_size'] = 'col-sm-5';
                        $vars['error_size'] = 'col-sm-4';
                    }
                    break;

            }

            $parsePattern = function ($pattern, $vars) {
                $params = array();
                $vals = array();
                foreach ($vars as $var => $val) {
                    $params[] = '%' . $var . '%';
                    $vals[] = $val;
                }
                $result = str_replace($params, $vals, $pattern);
                return $result;
            };

            $vars['element_name'] = $element->getName();
            $vars['element_content'] = $this->view->formElement($element);
            $vars['error_content'] = $this->view->formElementErrors($element);
            $vars['error_class'] = $element->getMessages() ? ' has-error' : '';
            $vars['desc_content'] = $element->getAttribute('description');
            $vars['desc_html'] = $parsePattern($descPattern, $vars);
            $vars['label_content'] = $element->getLabel();
            $vars['mark_required'] = $element->getAttribute('required') ? $markRequired : '';
            $vars['label_html'] = $parsePattern($labelPattern, $vars);
            $vars['element_html'] = $parsePattern($elementPattern, $vars);

            $rendered = $parsePattern($renderPattern, $vars);

            return $rendered;
        };

        $renderRow = function ($element) use ($renderElement) {
            $return = '';
            if (method_exists($element, 'getElements')) {
                $return .= '<legend>' .  $this->view->formLabel($element) . '</legend>';

                $eles = $element->elementList();
                foreach ($eles['active'] as $ele) {
                    $return .= $renderElement($ele);
                }
            } else {
                $return .= $renderElement($element);
            }

            return $return;
        };

        // Starts rendering
        $html = '';

        // Render warning messages at top
        $hiddenMessages = $form->getHiddenMessages();
        if ($hiddenMessages) {
            $htmlAlert = '<div class="alert alert-danger">' . PHP_EOL;
            if (!empty($hiddenMessages['security'])) {
                foreach ($hiddenMessages['security'] as $elMessage) {
                    $htmlAlert .= '<p>' . $elMessage . '</p>' . PHP_EOL;
                }
                unset($hiddenMessages['security']);
            }
            foreach ($hiddenMessages as $elName => $elMessages) {
                $htmlAlert .= '<h4>' . $elName . '</h4>' . PHP_EOL;
                $htmlAlert .= '<ol>' . PHP_EOL;
                foreach ($elMessages as $elMessage) {
                    $htmlAlert .= '<li>' . $elMessage . '</li>' . PHP_EOL;
                }
                $htmlAlert .= '</ol>' . PHP_EOL;
            }
            $htmlAlert .= '</div>' . PHP_EOL;

            $html .= $htmlAlert;
        }

        if ('popup' == $style) {
            $html .= <<<EOT
<div class="modal-dialog">
    <div class="modal-content">
EOT;
            $modalHeader = <<<EOT
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" title="%s">&times;</button>
            <h4 class="modal-title">%s</h4>
        </div>
EOT;
            $title = $form->getLabel() ?: __('Form');
            $html .= sprintf($modalHeader, __('Close'), _escape($title)) . PHP_EOL;
            $html .= '<div class="modal-body">' . PHP_EOL;
        }

        // Render form content
        $html .= $this->openTag($form) . PHP_EOL;

        // Render elements directly
        if (!$groups) {
            foreach ($elements['active'] as $element) {
                $html .= $renderRow($element);
            }
            // Render groups
        } else {
            foreach ($groups as $group) {
                $html .= '<legend>' . _escape($group['label']) . '</legend>' . PHP_EOL;
                foreach ($group['elements'] as $name) {
                    $element = $form->get($name);
                    $html .= $renderRow($element) . PHP_EOL;
                }
            }
        }

        // Render hidden elements
        foreach ($elements['hidden'] as $element) {
            $html .= $this->view->formElement($element) . PHP_EOL;
        }

        // Render submit button
        if (!empty($elements['submit'])) {
            $submit = $this->view->formElement($elements['submit']);
            $cancel = !empty($elements['cancel']) ? $this->view->formElement($elements['cancel']) : '';
            switch ($style) {
                case 'popup':
                    $waiting = '<img src="' . $this->view->assetTheme('image/wait.gif') . '" class="hide">';
                    $htmlSubmit = sprintf(
                        '<div class="modal-footer">' . '%s%s%s' . '</div>',
                        $waiting,
                        $submit,
                        $cancel
                    );
                    break;

                case 'horizontal':
                    $submitSize = ('multiple' == $column)
                        ? 'col-md-offset-2 col-md-10'
                        : 'col-sm-offset-3 col-md-4';
                    $htmlSubmit = sprintf(
                        '<div class="form-group"><div class="%s">%s%s</div></div>',
                        $submitSize,
                        $submit,
                        $cancel
                    );
                    break;

                default:
                    $htmlSubmit = sprintf(
                        '<div class="form-group">%s%s</div>',
                        $submit,
                        $cancel
                    );
                    break;
            }

            $html .= $htmlSubmit;
        }

        // Close of form content
        $html .= $this->closeTag() . PHP_EOL;

        if ('popup' == $style) {
            $script =<<<EOT
<script>
var formModule = (function($) {
    var formModule = {},
        form = $("#%s"),
        imgWait = form.find("img.hide");
    var items = form.find(".form-group").removeClass("has-error").find(".help-block").html("").end();
    form.submit(function(e) {
        imgWait.removeClass("hide");
        e.preventDefault();
        $.post(form.attr("action"), form.serialize()).done(function(result) {
            result = $.parseJSON(result);
            if (result.status == 1) {
                formModule.success(result);
            } else {
                var msg = result.message;
                for (var i in msg) {
                    if (msg.hasOwnProperty(i)) {
                        items.filter("[data-name=" + i + "]").addClass("has-error").find(".help-block").html(msg[i][0]);
                    }
                }
                formModule.fail();
            }
            imgWait.addClass("hide");
        });
    });
    /**
     * Two port
     * success: This event fires immediately when form submit success
     * fail:  This event fires immediately when form submit has wrong
     */
    formModule.success = function() {};
    formModule.fail = function() {};
    return formModule;
})(jQuery)
</script>
EOT;
            $html .= '</div></div>';
            $html .= sprintf($script, $form->getAttribute('id'));
        }

        return $html;
    }
}
