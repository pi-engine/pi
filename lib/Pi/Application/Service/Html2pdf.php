<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Service;

use Pi;

/**
 * @author Mickaël STAMM <contact@sta2m.com>
 */
 
 
class Html2pdf extends AbstractService
{
    public function pdf($template, $data, $name = 'document.pdf')
    {
        require_once Pi::path('vendor') . '/autoload.php';
        
        $moduletemplate = new \Pi\View\Resolver\ModuleTemplate();
        $template = $moduletemplate->resolve($template, Pi::engine()->application()
                ->getServiceManager()->get('view_manager')->getRenderer(), strstr($template, 'front'));
        
        // Get HTML
        $html = Pi::service('view')->render($template, $data);

        // Generate PDF
        $html2pdf = new \Spipu\Html2Pdf\Html2Pdf();
        $html2pdf->writeHTML($html);
        $html2pdf->pdf->SetJPEGQuality(1);
        $html2pdf->pdf->setImageScale(1.53); 
        
        $html2pdf->output($name);
        exit;
    }
}
