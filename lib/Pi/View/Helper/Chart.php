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
 * Helper for draw charts
 *
 * $this->chart($type, $data, $options, $htmlClass);
 *
 * @see http://www.chartjs.org
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
class Chart extends AbstractHelper
{
    /**
     * Load chart script
     *
     * @param   string $type
     * @param   array $data
     * @param   array $options
     * @param   string $htmlClass
     *
     * @return  $this
     */
    public function __invoke($type, $data, $options = [], $htmlClass = 'pi-chart')
    {
        // Set uniqid
        $id = uniqid('piChart');

        // Sat data and option
        $pattern     = '/"([a-zA-Z]+[a-zA-Z0-9_]*)":/';
        $replacement = '$1:';
        $data        = preg_replace($pattern, $replacement, json_encode($data));
        $options     = empty($options) ? '{}' : preg_replace($pattern, $replacement, json_encode($options));

        // Set script
        $script
                = <<<'EOT'
(function ($) {
    $(document).ready(function () {
        var ctx = document.getElementById('%s').getContext('2d');
        var %s = new Chart(ctx, {
            type: '%s',
            data: %s,
            options: %s
        });
    });
})(jQuery)
EOT;
        $script = sprintf(
            $script,
            $id,
            $id,
            $type,
            $data,
            $options
        );

        // Load chart
        $this->view->jQuery();
        $this->view->js(pi::url('static/vendor/chart/Chart.min.js'));
        $this->view->footScript()->appendScript($script);

        // render html
        $htmlTemplate
            = <<<'EOT'
<div class="pi-chart-section">
    <canvas id="%s" class="%s"></canvas>
</div>
EOT;

        $content = sprintf($htmlTemplate, $id, $htmlClass);

        return $content;
    }
}