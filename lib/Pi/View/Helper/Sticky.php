<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
    $("#sticky-sidebar").hcSticky({
        responsive : true,
        top: $('#pi-header nav').height() + 20,
        stickTo: $('#sticky-container'),
        offResolutions: -992
    });
    
    $('#sticky-sidebar div.modal').insertAfter('.wrapper-sticky');
    
  });
EOT;

        $this->view->js(pi::url('static/vendor/jquery/extension/jquery.hc-sticky.min.js'));
        $this->view->footScript()->appendScript($script);

        return $this;
    }
}
