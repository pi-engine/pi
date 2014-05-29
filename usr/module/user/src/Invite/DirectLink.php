<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Invite;

use Pi;

/**
 * Direct link class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class DirectLink extends AbstractInvite
{
    /**
     * {inheritDoc}
     */
    protected $identifier = 'direct-link';
    
    /**
     * {inheritDoc}
     */
    public function generate($appkey = '', $uid = 0)
    {
        $uid = $uid ?: Pi::user()->getId();
        if (empty($uid)) {
            return '';
        }
        
        $data = array(
            'appkey'  => $appkey ?: Pi::config('identifier'),
            'inviter' => $uid,
        );
        
        $ikey = base64_encode(json_encode($data));
        $url  = Pi::user()->getUrl('register', array(
            'ikey'     => $ikey,
            'mode'     => $this->identifier,
            'redirect' => Pi::user()->getUrl('profile'),
        ));
        
        return Pi::url($url);
    }
    
    /**
     * {inheritDoc}
     */
    public function resolve($params = '')
    {
        $params = $params ?: Pi::service('user')->getRouteMatch()->getParam('ikey', '');
        if (empty($params)) {
            return array();
        }
        
        $result = base64_decode($params);
        
        return json_decode($result, true);
    }
    
    /**
     * {inheritDoc}
     */
    public function render($template = '', array $variables = array())
    {
        $uid    = isset($variables['uid']) ? $variables['uid'] : 0;
        $appkey = isset($variables['appkey']) ? $variables['appkey'] : '';
        
        if (!isset($variables['invite_url'])) {
            $url = $this->generate($appkey, $uid);
            $variables['invite_url'] = $url;
        }
        
        if (!empty($template)) {
            $content = Pi::service('view')->render($template, $variables);
        } else {
            $id      = $this->identifier . '-invite';
            $button  = __('Copy Link');
            $success = __('Copy successful, please use CRTL+V to paste it.');
            $failed  = __('Your browser donnot support copy script, please copy it manually by CTRL+C.');
            $title   = __('Please send this link to your friend.');
            $content =<<<EOD
<div style="margin: 5px 0 10px 0">{$title}</div>
<div id="{$id}" class="form-inline row">
    <div class="form-group col-md-9">
        <input type="text" name="{$this->identifier}" value="{$variables['invite_url']}" class="form-control" style="width: 100%">
    </div>
    <div class="form-group">
        <button class="btn btn-info">{$button}</button>
    </div>
    <script>
        jQuery(function($){
            $("#{$id} button").click(function() {
                var content = $("#{$id} input").val();
                if (window.clipboardData) {
                    window.clipboardData.clearData();
                    window.clipboardData.setData('Text', content);
                } else if (navigator.userAgent.indexOf('opera') != -1) {
                    window.location = content;
                } else if (window.netscape) {
                    try {
                        netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
                    } catch (e) {
                        alert("{$failed}");
                    }
                    return;
                }
                alert("{$success}");
            });
            $("#{$id} input").click(function() {
                $(this).select();
            });
        });
    </script>
</div>
EOD;
        }
        
        return $content;
    }
    
    /**
     * {inheritDoc}
     */
    public function customTemplate()
    {
        return '';
    }
    
    /**
     * {inheritDoc}
     */
    public function customParams()
    {
        return array();
    }
}
