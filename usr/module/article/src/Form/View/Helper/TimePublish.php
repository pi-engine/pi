<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Form
 */

namespace Module\Article\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\AbstractHelper;
use Pi;

/**
 * Time publish element helper
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class TimePublish extends AbstractHelper
{
    /**
     * Invoke helper as function
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface|null $element
     * @return string|self
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }

    /**
     * {@inheritDoc}
     */
    public function render(ElementInterface $element)
    {
        $locale = Pi::config('locale');
        if (false !== strpos('-', $locale)) {
            $list = explode('-', $locale, 2);
            $locale = $list[0] . '-' . strtoupper($list[1]);
        }
        $datepickerLocale = sprintf(
            'datepicker/locales/bootstrap-datepicker.%s.js',
            $locale
        );
        $this->view->bootstrap(array(
            'datepicker/datepicker.css',
            'datepicker/bootstrap-datepicker.js',
            $datepickerLocale,
        ));

        $required = $element->getAttribute('required');
        
        $time = $element->getValue();
        $date = $hour = $min = $second = '';
        if ($time) {
            list($date, $dateTime) = explode(' ', $time);
            if ($dateTime) {
                list($hour, $min, $second) = explode(':', $dateTime);
            }
        }
        
        $html = <<<EOT
<div id="time-publish-element" style="width: 250px">
    <input type="hidden" value="%s" name="%s" %s %s>
    <div class="pull-right">
        <div class="time-control-item">
            <input type="text" value="{$hour}" class="input-small text-hour form-control" maxlength="2">
            <div class="text-muted" style="padding: 0 5px; width: 9px; font-size: 12px">
                <span class="fa fa-chevron-up hour-sort" data-action="1"></span>
                <span class="fa fa-chevron-down hour-sort"></span>
            </div>
        </div>
        <div class="time-control-item" style="margin-left: 10px">
            <input type="text" value="{$min}" class="input-small text-minute form-control" maxlength="2">
            <div class="text-muted" style="padding: 0 5px; width: 9px; font-size: 12px">
                <span class="fa fa-chevron-up minute-sort" data-action="1"></span>
                <span class="fa fa-chevron-down minute-sort"></span>
            </div>
        </div>
    </div>
    <input id="datepicker" class="form-control" value="{$date}" type="text" style="width: 110px; margin: 0;">
</div>
<script type="text/javascript">
(function($) {
    var TimePublishView = Backbone.View.extend({
        el  : $("#time-publish-element"),
        events : {
            "click .hour-sort"   : "hourAction",
            "click .minute-sort" : "minuteAction"
        },
        initialize : function() {
            _.bindAll(this);
        },
        hourAction  : function(e) {
            var el = this.$(".text-hour"),
                v;
            if ($(e.target).attr("data-action")) {
                v = parseInt(el.val()) - 1;
                v = v < 0 ? 23 : v;
                el.val(v);
            } else {
                v = parseInt(el.val()) + 1;
                v = v > 24 ? 0 : v; 
                el.val(v);
            }
        },
        minuteAction : function(e) {
            var el = this.$(".text-minute"),
                v;
            if ($(e.target).attr("data-action")) {
                v = parseInt(el.val()) - 1;
                v = v < 0 ? 59 : v;
                el.val(v); 
                el.val(v);
            } else {
                v = parseInt(el.val()) + 1;
                v = v > 59 ? 0 : v;  
                el.val(v); 
            }
        }
    });
    new TimePublishView;
})(jQuery)
</script>
EOT;

        return sprintf(
            $html,
            $element->getValue(),
            $element->getName(),
            $this->createAttributesString($element->getAttributes()),
            $required ? 'required="required"' : ''
        );
    }
}
