<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Form
 */

namespace Custom\User\Form\View\Helper;

use Zend\Form\ElementInterface;
//use Zend\Form\Exception;
use Zend\Form\View\Helper\AbstractHelper;
use Pi;

/**
 * Start/end time element helper
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class StartTime extends AbstractHelper
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
        $assetModule = $this->view->plugin('assetModule');
        $this->view->plugin('js')->load(
            $assetModule('common/eefocus-time.min.js')
        );
        $id = md5(uniqid());
        $maxYear = date('Y');

        $html = <<<'EOT'
        <div class="form-inline" id="%s"></div>
        <script>
        new eefocus.StartTime("%s", %s, "%s", "%s");
        </script>
EOT;

        return sprintf(
            $html,
            $id,
            $id,
            $maxYear,
            $element->getName(),
            $element->getValue()
        );
    }
}
