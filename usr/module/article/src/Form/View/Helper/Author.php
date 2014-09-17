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
 * Author element helper
 * Helper cannot be used in other module
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Author extends AbstractHelper
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
        $this->view->jQuery();

        $required = $element->getAttribute('required');
        $url      = Pi::service('url')->assemble('default', array(
            'controller' => 'ajax',
            'action'     => 'get.fuzzy.author',
        ));
        $authorId = $element->getValue();
        $name     = '';
        if ($authorId) {
            $module = Pi::service('module')->current();
            $row    = Pi::model('author', $module)->find($authorId);
            $name   = $row->name . "[{$row->id}]";
        }
        
        $html = <<<EOT
<div id="author-element">
    <input type="text" value="{$name}" class="author form-control" %s>
    <input type="hidden" value="{$authorId}" name="%s" %s>
</div>
<script type="text/javascript">
(function($) {
    var AuthorView = Backbone.View.extend({
        el  : $("#author-element"),
        events : {
            "keyup .author" : "search"
        },
        initialize : function() {
            _.bindAll(this);
            this.search();
        },
        search : function(e) {
            var obj = $(".author");
            this.$('.author').typeahead({
                source : function (query, process) {
                    var self = this;
                    return $.getJSON("{$url}name-" + obj.val(), function (resp) {
                        self.data = resp.data;
                        var items = new Array();
                        for (i in resp.data) {
                            items[i] = resp.data[i].name;
                        }
                        return process(items);
                    });
                },
                updater : function(item) {
                    var id;
                    for (i in this.data) {
                        if (item === this.data[i].name) {
                            id = this.data[i].id;
                            break;
                        }
                    }
                    $("[name=author]").val(id);
                    return item;
                }
            }).on("blur", function() {
                var el = $(this),
                    v  = $.trim(el.val());
                var data = el.data("typeahead").data;
                var selected = 0;
                for (var i in data) {
                    if (v === data[i].name) {
                        selected = data[i].id;
                        break;
                    }
                }
                if (!selected) {
                    el.val("");
                    $("[name=author]").val("");
                } else {
                    $("[name=author]").val(selected);
                }
            }).on("focus", function() {
                var el = $(this);
                el.data("typeahead").data=[{
                    name : $.trim(el.val()),
                    id   : page.$("[name=author]").val()
                }];
            });
        }
    });
    new AuthorView;
})(jQuery)
</script>
EOT;

        return sprintf(
            $html,
            $required ? 'required="required"' : '',
            $element->getName(),
            $this->createAttributesString($element->getAttributes())
        );
    }
}
