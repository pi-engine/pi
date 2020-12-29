<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Service;

use Pi;
use Pi\View\Resolver\ModuleTemplate;
use Spipu\Html2Pdf\Html2Pdf as SpipuHtml2Pdf;

/**
 * @author Mickaël STAMM <contact@sta2m.com>
 */
class Html2pdf extends AbstractService
{
    public function pdf($template, $data, $name = 'document.pdf')
    {
        ob_end_clean();
        //require_once Pi::path('vendor') . '/autoload.php';

        $moduleTemplate = new ModuleTemplate();
        $template       = $moduleTemplate->resolve(
            $template,
            Pi::engine()->application()->getServiceManager()->get('view_manager')->getRenderer(),
            strstr($template, 'front')
        );

        // Get HTML
        $html = Pi::service('view')->render($template, $data);

        // Generate PDF
        $html2pdf = new SpipuHtml2Pdf();
        $html2pdf->writeHTML($html);
        $html2pdf->pdf->SetJPEGQuality(90);
        $html2pdf->pdf->setImageScale(1.53);

        $html2pdf->output($name);
        exit;
    }
}
