<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHelper;

/**
 * Helper for load Sticky
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */

class Sticky extends AbstractHelper
{
    /**
     * Load Sticky scripts
     *
     * @param   array   $locations
     * @param   string  $apiKey
     * @param   string  $type       point|route|list
     * @param   array   $option     Set custom options
     *
     * @return  $this
     */
    public function __invoke(
        $option = array()
    ) {

        $script = <<<'EOT'
$(document).ready(function(){
    var affixWrapperHeight = 0;
    
    var affixWrapper = $('#affixWrapper');
    
    if(affixWrapper.length > 0){
        affixWrapper.find('.nav').addClass('forceVisible');
        var affixWrapperHeight = affixWrapper.height();
        affixWrapper.find('.nav').removeClass('forceVisible');
    }

    var Sticky = new hcSticky('#sticky-sidebar', {
        responsive : true,
        top: $('#pi-header nav').height() + affixWrapperHeight + 20,
        stickTo: '#sticky-container',
        queries: {
            992: {
              disable: true
            }
        }
    });

    $('#sticky-sidebar div.modal').insertAfter('.wrapper-sticky');
    
  });
EOT;

        $this->view->js(pi::url('static/vendor/jquery/extension/jquery.hc-sticky.min.js'));
        $this->view->footScript()->appendScript($script);

        return $this;
    }
}
