<?php
/**
 * Feed controller class
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Pi\Mvc
 * @subpackage      Controller
 * @version         $Id$
 */

namespace Pi\Mvc\Controller;

use Pi;
//use Zend\EventManager\EventManagerInterface;

//use Zend\Http\PhpEnvironment\Response as HttpResponse;
//use Zend\Mvc\Exception;
use Zend\Mvc\MvcEvent;
//use Zend\Stdlib\RequestInterface as Request;
//use Zend\Stdlib\ResponseInterface as Response;
use Zend\View\Model\FeedModel as ViewModel;

/**
 * Basic feed controller
 */
abstract class FeedController extends ActionController
{
    /**
     * Execute the request
     *
     * @param  MvcEvent $e
     * @return ViewModel
     */
    public function onDispatch(MvcEvent $e)
    {
        $this->setViewModel($e);
        $actionResponse = parent::onDispatch($e);

        // Collect directly returned array content
        if (null !== $actionResponse && is_array($actionResponse)) {
            $this->view()->assign($actionResponse);
            $actionResponse = null;
        }
        // Canonize feed model
        $viewModel = $this->view()->getViewModel();
        $content = $viewModel->getVariable('content');
        if ($content) {
            $entries = $viewModel->getVariable('entries') ?: array();
            $entries[] = array(
                'content'   => $content,
            );
            $viewModel->setVariable('entries', $entries);
            $viewModel->setVariable('content', null);
        }
        $e->setResult($viewModel);
        //return $viewModel;
    }

    /**
     * Create FeedModel and initialize variables, register to ViewEvent
     *
     * @param  MvcEvent $e
     * @return FeedController
     */
    protected function setViewModel(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        $feedType = $routeMatch->getParam('type', 'rss');

        // Preset feed variables
        $variables = array(
            'copyright'     => Pi::config('copyright', 'meta') ?: Pi::config('sitename'),
            'description'   => Pi::config('description', 'meta') ?: Pi::config('slogan'),
            'authors'       => array(
                array(
                    'name'      => Pi::config('author', 'meta'),
                    'email'     => Pi::config('adminmail'),
                ),
            ),
            'generator'     => array(
                'name'      => 'Pi Engine with ZF2',
                'version'   => Pi::config('version'),
                'uri'       => 'http://www.xoopsengine.org',
            ),
            'image'         => array(
                'uri'       => Pi::url('static', true) . '/image/logo.png',
                'title'     => Pi::config('sitename'),
                'link'      => Pi::url('www', true),
            ),

            'language'      => Pi::config('locale'),
            'link'          => Pi::url('www', true),
            'feed_link'     => array(
                'link'      => Pi::url($this->url('feed', $routeMatch->getParams()), true),
                'type'      => $feedType,
            ),
            'title'         => sprintf(__('Feed of %s - %s'), Pi::config('sitename'), Pi::config('slogan')),
            'encoding'      => Pi::config('charset'),
            'base_url'      => Pi::url('www', true),
        );
        $options = array(
                'feed_type' => $feedType,
        );

        $viewModel = new ViewModel($variables, $options);
        $this->view()->setViewModel($viewModel)->setTemplate(false);

        return $this;
    }
}
