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
    public function pdf($template, $data)
    {
        require_once Pi::path('vendor') . '/autoload.php';
        
        // Get HTML
        $html = Pi::service('view')->render($template, $data);
        //echo $html;exit;
        // Generate PDF
        $html2pdf = new \Spipu\Html2Pdf\Html2Pdf();
        $html2pdf->writeHTML($html);
        $html2pdf->output();
        exit;
    }
}
