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

use Pi\View\Resolver\ModuleTemplate as ModuleTemplateResolver;
use Zend\View\Helper\AbstractHelper;

/**
 * Helper for include SVG image and set title property
 *
 * Usage inside a phtml template
 *
 * ```
 *  $this->svg($path, $title);
 * ```
 *
 * @author Mickaël STAMM <contact@sta2m.com>
 */
class Svg extends AbstractHelper
{
    /**
     * 
     * @return  string
     */
    public function __invoke($path, $title = null)
    {
        $svg = new \SimpleXMLElement(file_get_contents($path));
        if ($title) {
            $svg->title[0] = $title;
        }
        return $svg->asXml(); 
    }
}
