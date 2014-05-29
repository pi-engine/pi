<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Invite;

/**
 * Abstract invite class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
abstract class AbstractInvite
{
    /**
     * Invitation mode identifier
     * e.g.
     * Class name: AbstractInvite => identifier value: abstract-invite
     * @var string 
     */
    protected $identifier = '';
    
    /**
     * Generate invitation link
     * 
     * @param $appkey string  site appkey
     * @param $uid    int     user ID of inviter
     * @return string
     */
    abstract public function generate($appkey = '', $uid = 0);
    
    /**
     * Resolve passed encode string into available values
     * 
     * @param $params string  encoded params including inviter info
     * @return array  array('inviter' => '', 'appkey' => '')
     */
    abstract public function resolve($params = '');
    
    /**
     * Render HTML tag to display in invitation page for inviting
     * 
     * @param $template  string|array  template to use
     * @param $variables array         variables for rendering html tag
     * @return string
     */
    abstract public function render($template = '', array $variables = array());
    
    /**
     * Customize template in invitation page.
     * Return empty string if you donot want to customize it
     * 
     * e.g. 1
     * array(
     *     'module'  => 'user',
     *     'section' => 'front',
     *     'file'    => 'abstract-invite.phtml',
     * );
     * e.g. 2
     * 'user:front/abstract-invite.phtml'
     * 
     * @return string|array
     */
    abstract public function customTemplate();
    
    /**
     * Customize template varables for render HTML which will be displayed in invitation page.
     * Just return empty if donot want to customize it
     * 
     * @return array
     */
    abstract public function customParams();
}
