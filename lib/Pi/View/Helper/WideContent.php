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
 * WideContent
 *
 * Return html content or register as global for displaying behind de main container in main template / theme
 *
 * To be used with some template code. For example :
 * --------------
 * ob_start();
 * include('partial/category-wide-header.phtml');
 * $wideContent = ob_get_clean();
 * echo $this->wideContent($wideContent, $module);
 * --------------
 *
 * @package Pi\View\Helper
 */
class WideContent extends AbstractHelper
{
    public function __invoke(
        $content, $module) {

        // Get config
        $config = Pi::service('registry')->config->read($module);

        if(!empty($config['wide_content']) && $config['wide_content'] == 1){
            $GLOBALS['wideContent'] = $content;
            return null;
        }

        return $content;
    }
}
