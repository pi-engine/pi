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
use Laminas\View\Helper\AbstractHelper;

/**
 * WideContent
 *
 * Return html content (or register as global) for displaying behind the main container in main template / theme
 * Turnaround to be able to display wide image (full width) in template, for instance, despite the usage of BS container class in standard layout-front template
 * This avoids us to have to change all templates in all modules for this use case : time saving
 * To be used with some template code. For example :
 * --------------
 * ob_start();
 * include('partial/category-wide-header.phtml');
 * $wideContent = ob_get_clean();
 * echo $this->wideContent($wideContent, $module);
 * --------------
 *
 * @package Pi\View\Helper
 * @author esprit-dev / marc-pi
 */
class WideContent extends AbstractHelper
{
    public function __invoke(
        $content, $module)
    {

        // Get config
        $config = Pi::service('registry')->config->read($module);

        if (!empty($config['wide_content']) && $config['wide_content'] == 1) {
            $GLOBALS['wideContent'] = $content;
            return null;
        }

        return $content;
    }
}
