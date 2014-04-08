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
use Zend\View\Helper\AbstractHtmlElement;

/**
 Helper for interaction (between user and page) bar
 *
 * Usage inside a phtml template
 *
 * ```
 *  // Load helper for current request URI
 *  $interaction = $this->interaction();
 *
 *  // Load for specific object
 *  $interaction = $this->interaction(
 *      array('module' => <module>, 'id' => <id>, 'type' => <type>)
 *  );
 *
 *  // Load for current request URI with specified id and/or type
 *  $interaction = $this->interaction(
 *      array('id' => <id>, 'type' => <type>)
 *  );
 *
 *  // Render specific actions
 *  $interaction->render(
 *      array('like', 'rate', 'checkin', 'view', 'plusone', 'twitter')
 *  );
 *
 *  // Render default actions
 *  $interaction->render();
 *
 *  // Load/Render specific actions for specific object
 *  $this->interaction(
 *      array('module' => <module>, 'id' => <id>, 'type' => <type>),
 *      array('like', 'rate', 'checkin', 'view', 'plusone', 'twitter')
 *  );
 *
 *  // Load/Render specific actions for current request URI
 *  $this->interaction(
 *      '',
 *      array('like', 'rate')
 *  );
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Interaction extends AbstractHtmlElement
{
    /**
     * Locator for object to be interacted with
     *
     * @var string|array
     */
    protected $locator;

    /**
     * Load interaction helper, and render if actions are specified
     *
     * @param string|array $locator Locator params: module, type, id
     * @param array|null $actions

     * @return $this
     */
    public function __invoke($locator = '', $actions = null)
    {
        $this->locator = $locator;
        if (null === $actions) {
            return $this;
        }

        $this->render((array) $actions);

        return $this;
    }

    /**
     * Render interaction bar for ajax request
     *
     * Action list
     *
     *  - like
     *  - share
     *  - rate
     *  - view
     *  - checkin
     *  - plusone
     *  - twitter
     *
     * @param array $actions
     *
     * @throws \Exception
     * @return string
     */
    public function render(array $actions = array())
    {
        // Canonize locator against module, id, type
        if (is_string($this->locator)) {
            $routeMatch = Pi::service('url')->match($this->locator);
            $params = array(
                'module'    => $routeMatch->getModule(),
                'id'        => $routeMatch->getParam('id'),
                'type'      => $routeMatch->getParam('type'),
            );
        } else {
            $routeMatch = Pi::engine()->application()->getRouteMatch();
            $params = (array) $this->locator;
            if (empty($params['module'])) {
                $params['module'] = $routeMatch->getModule();
            }
            if (empty($params['id'])) {
                $params['id'] = $routeMatch->getParam('id');
            }
            if (!isset($params['type'])) {
                $params['type'] = $routeMatch->getParam('type');
            }
        }
        $this->locator = $params;

        throw new \Exception(__METHOD__ . ' not implemented yet.');

        // Build html
        $html = '';
        return $html;
    }
}
