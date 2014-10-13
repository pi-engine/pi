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
     * @param array|string|false $options Options for rendering: `style` - single, multiple, popup, raw
     */
    public function render(FormInterface $form, $options = array())
    {
        // Canonize options
        if (!is_array($options)) {
            if (is_string($options)) {
                $options = array('style' => $options);
            } elseif (!$options) {
                $options = array('style' => '');
            }
        }
        if (!isset($options['style'])) {
            $options['style'] = 'single';
        }
        $style = $options['style'];

        // Render Zend Form directly if style is not desired
        if (!in_array($style, array('single', 'multiple', 'popup'))) {
            return parent::render($form);
        }

        $class = $form->getAttribute('class');
        $class = ($class ? $class . ' ' : '')
            . (empty($options['class'])
                ? 'form-horizontal'
                : $options['class']
            );
        $attributes = array(
            'class' => $class,
        );

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
        $markRequired = '<i class="text-danger" style="margin-right: 5px;">*</i>';
        $this->view->FormElementErrors()
            ->setMessageOpenFormat('')
            ->setMessageCloseString('');

        // Anonymous functions for element/group rendering
        $renderElement = function ($element) use (
            $markRequired,
            $emptyControl,
            $style
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
            $labelPattern = '<label class="%label_size% control-label">%mark_required%%label_content%</label>';
            $descPattern = '<div class="text-muted">%desc_content%</div>';
            $elementPattern =<<<EOT
<div class="%element_size% js-form-element">
    %element_content%%desc_html%
</div>
<div class="%error_size% help-block">%error_content%</div>
EOT;
            $vars = array();
            $vars['element_name'] = $element->getName();
            $vars['element_content'] = $this->view->formElement($element);
            $vars['error_content'] = $this->view->formElementErrors($element);
            $vars['error_class'] = $element->getMessages() ? ' has-error' : '';

            if ('popup' == $style) {
                $vars['label_size'] = 'col-md-3';
                $vars['element_size'] = 'col-sm-8';
                $vars['error_size'] = 'col-sm-8';
                if ($type == 'multi_checkbox' || $type == 'checkbox') {
                    $vars['element_size'] = 'col-sm-8';
                    $vars['error_size'] = '';
                }
                $labelPattern =<<<EOT
<label class="%label_size% control-label">
    %mark_required%%label_content%
</label>
%desc_html%
EOT;
                $descPattern =<<<EOT
<i class="icon-question-sign" data-original-title="%desc_content%"></i>
EOT;
                $elementPattern =<<<EOT
<div class="%element_size% js-form-element">
    %element_content%
</div>
<div class="%error_size% help-block">%error_content%</div>
EOT;
            } elseif ('multiple' == $style) {
                $vars['label_size'] = 'col-md-2';
                $vars['element_size'] = 'col-md-4';
                $vars['error_size'] = 'col-md-4';
            } else {
                $vars['label_size'] = 'col-sm-3 col-lg-2';
                $vars['element_size'] = 'col-sm-5';
                $vars['error_size'] = 'col-sm-4';
                if ($type == 'multi_checkbox' || $type == 'checkbox') {
                    $vars['element_size'] = 'col-sm-9';
                    $vars['error_size'] = '';
                    $elementPattern =<<<EOT
<div class="%element_size% js-form-element">
    %element_content%%desc_html%
    <div class="%error_size% help-block">%error_content%</div>
</div>
EOT;
                }
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
            if ('popup' == $style) {
                $waiting = '<img src="' . $this->view->assetTheme('image/wait.gif') . '" class="hide">';
                $htmlSubmit = sprintf(
                    '<div class="modal-footer">' . '%s%s%s' . '</div>',
                    $waiting,
                    $submit,
                    $cancel
                );
            } else {
                $submitSize = ('multiple' == $style)
                    ? 'col-md-offset-2 col-md-10'
                    : 'col-sm-offset-3 col-lg-offset-2 col-md-4';
                $htmlSubmit = sprintf(
                    '<div class="form-group"><div class="%s">%s%s</div></div>',
                    $submitSize,
                    $submit,
                    $cancel
                );
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
