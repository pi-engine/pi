<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Invite controller
 * 
 * Feature:
 * 1. Display invitation link
 * 2. Send invitation message
 */
class InviteController extends ActionController
{
    /**
     * Display invitation link of all modes allowed
     *
     */
    public function indexAction()
    {
        $isActive = $this->config('enable_invite');
        if (!$isActive) {
            return $this->jumpTo404();
        }
        
        $content = array();
        $modes = $this->config('invite_mode');
        foreach ($modes as $key) {
            $name = preg_replace('/[-_]/', ' ', $key);
            $name = str_replace(' ', '', ucwords($name));
            $class = sprintf('Custom\User\Invite\%s', $name);
            if (!class_exists($class)) {
                $class = sprintf('Module\User\invite\%s', $name);
                if (!class_exists($class)) {
                    throw new \Exception(sprintf(__('Class %s not exists.'), $class));
                }
            }
            
            // Render html
            $invitHandle = new $class;
            $template  = $invitHandle->customTemplate();
            $variables = $invitHandle->customParams();
            $appkey    = $this->config('site_appkey');
            $url       = $invitHandle->generate($appkey);
            $variables['invite_url'] = $url;
            
            $content[$key] = $invitHandle->render($template, $variables);
        }
        
        $this->view()->assign(array(
            'title'     => __('Invite your friend to reigster'),
            'content'   => $content,
        ));
    }
}
