<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Setup\Controller;

use Pi;
use Pi\Application\Installer\Module as ModuleInstaller;
use Pi\Acl\Acl;

/**
 * Admin controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Admin extends AbstractController
{
    protected $hasBootstrap = true;

    public function init()
    {
        $db = Pi::service('database')->db();
        Pi::entity('db', $db);

        $vars = $this->wizard->getPersist('siteconfig');
        if (empty($vars)) {
            $vars['adminusername'] = 'admin';
            $vars['adminname'] = _s('Pi Admin');
            /*
            $hostname = preg_replace('/^www\./i', '', $_SERVER['SERVER_NAME']);
            if (false === strpos($hostname, '.')) {
                $hostname .= '.com';
            }
            $vars['adminmail'] = $vars['adminname'] . '@' . $hostname;
            */
            $vars['adminmail'] = '';
            $vars['adminpass'] = $vars['adminpass2'] = '';
            $this->wizard->setPersist('siteconfig', $vars);
        }
        $this->vars = $vars;
    }

    public function clearAction()
    {
        $this->hasForm = true;
        if ($this->request->getPost('retry')) {
            $adapter = Pi::entity('db')->adapter();
            $tablePrefix = Pi::entity('db')->getTablePrefix();

            try {
                // Drop all tables
                $sql = sprintf(
                    'SHOW TABLES LIKE %s',
                    $adapter->getPlatform()->quoteValue($tablePrefix . '%%')
                );
                $resource = $adapter->query($sql)->getResource();
                $resource->execute();
                while ($row = $resource->fetch(\PDO::FETCH_NUM)) {
                    $adapter->query('DROP TABLE ' . $row[0], 'execute');
                }
                // Drop all views
                // ...
                // Drop all triggers
                // ...
            } catch (\Exception $e) {
                $this->content = '<p class="alert">'
                               . _s('System module uninstallation is failed. Please continue to try again.')
                               . '</p>'
                               . $e->getMessage()
                               . '<input type="hidden" name="page" value="admin" />'
                               . '<input type="hidden" name="retry" value="1" />'
                               . '<input type="hidden" name="action" value="clear" />';

                return;
            }
            $this->loadForm();
        }
    }

    public function setAction()
    {
        $var = $this->request->getParam('var');
        $val = $this->request->getParam('val', '');
        $this->vars[$var] = $val;
        $this->wizard->setPersist('siteconfig', $this->vars);

        echo 1;
    }

    public function checkAction()
    {
        $var = $this->request->getParam('var');
        $val = $this->vars[$var];
        $error = '';
        switch ($var) {
            case 'adminname':
                if (empty($val)) {
                    $error = _s('Information is required.');
                }
                break;
            case 'adminmail':
                if (empty($val)) {
                    $error = _s('Information is required.');
                } elseif (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
                    $error = _s('Invalid Email.');
                }
                break;
            case 'adminpass':
            case 'adminpass2':
                $v1 = $this->vars['adminpass'];
                $v2 = $this->vars['adminpass2'];
                if (empty($v1) || empty($v2)) {
                    $error = _s('Information is required.');
                } elseif ($v1 !== $v2) {
                    $error = _s('The two passwords do not match');
                }
                break;
            default:
                break;
        }
        echo $error;
    }

    public function submitAction()
    {
        $installer = new ModuleInstaller;
        $ret = $installer->install('system');
        if (!$ret) {
            $this->hasForm = true;
            $this->content = '<p class="alert">'
                           . _s('System module installation is failed. Please continue to try again.')
                           . '</p>'
                           . $installer->renderMessage()
                           . '<input type="hidden" name="page" value="admin" />'
                           . '<input type="hidden" name="retry" value="1" />'
                           . '<input type="hidden" name="action" value="clear" />';

            return;
        }

        $vars = $this->vars;
        $vars['adminusername'] = $this->request->getPost('adminusername');
        $vars['adminmail'] = $this->request->getPost('adminmail');
        $vars['adminpass'] = $this->request->getPost('adminpass');
        $vars['adminpass2'] = $this->request->getPost('adminpass2');
        $vars['adminname'] = $this->request->getPost('adminname');
        $this->wizard->setPersist('siteconfig', $vars);

        $error = array();
        if (empty($vars['adminusername'])) {
            $error['name'][] = _s('Username is required.');
        }
        if (empty($vars['adminname'])) {
            $error['name'][] = _s('Name is required.');
        }
        if (empty($vars['adminmail'])) {
            $error['email'][] = _s('Email is required.');
        }
        if (empty($vars['adminpass'])) {
            $error['pass'][] = _s('Password is required.');
        }
        if (!filter_var($vars['adminmail'], FILTER_VALIDATE_EMAIL)) {
            $error['email'][] = _s('Invalid Email.');
        }
        if ($vars['adminpass'] != $vars['adminpass2']) {
            $error['pass'][] = _s('The two passwords do not match');
        }
        if (!$error) {
            // Update global contact email
            $configModel = Pi::model('config');
            $configModel->update(
                array('value' => $vars['adminmail']),
                array('name' => 'adminmail')
            );
            $configModel->update(
                array('value' => $vars['adminname']),
                array('name' => 'adminname')
            );

            // Create root admin user
            $adminData = array(
                'identity'      => $vars['adminusername'],
                'credential'    => $vars['adminpass'],
                'email'         => $vars['adminmail'],
                'name'          => $vars['adminname'],
            );
            $uid = Pi::api('system', 'user')->addUser($adminData);
            $this->status = $uid ? true : false;
            Pi::api('system', 'user')->activateUser($uid);
            Pi::api('system', 'user')->setRole($uid, array(
                'admin' => 'admin',
                'front' => 'webmaster',
            ));

            // Create system accounts
            $hostname = preg_replace('/^www\./i', '', $_SERVER['SERVER_NAME']);
            $accounts = array(
                'manager'   => array(
                    'name'  => __('Manager'),
                    'role'  => array(
                        'admin' => 'manager',
                        'front' => 'member',
                    ),
                ),
                'moderator' => array(
                    'name'  => __('Moderator'),
                    'role'  => array(
                        'admin' => 'moderator',
                        'front' => 'member',
                    ),
                ),
                'editor'    => array(
                    'name'  => __('Editor'),
                    'role'  => array(
                        'admin' => 'editor',
                        'front' => 'member',
                    ),
                ),
                'staff'     => array(
                    'name'  => __('Staff'),
                    'role'  => array(
                        'admin' => 'staff',
                        'front' => 'member',
                    ),
                ),
                'member'    => array(
                    'name'  => __('Member'),
                    'role'  => 'member',
                ),
            );
            foreach ($accounts as $identity => $data) {
                $userData = array(
                    'identity'      => $identity,
                    'email'         => $identity . '@' . $hostname,
                    'credential'    => $adminData['credential'],
                    'name'          => $data['name'],
                );
                $uid = Pi::api('system', 'user')->addUser($userData);
                Pi::api('system', 'user')->activateUser($uid);
                Pi::api('system', 'user')->setRole($uid, $data['role']);
            }
        }

        if ($this->status < 1) {
            $this->loadForm();
        }
    }

    public function indexAction()
    {
        $this->hasForm = true;

        $adapter = Pi::entity('db')->adapter();
        $tablePrefix = Pi::entity('db')->getTablePrefix();
        $sql = sprintf(
            'SHOW TABLES LIKE %s',
            $adapter->getPlatform()->quoteValue($tablePrefix . '%')
        );
        $resource = $adapter->query($sql)->getResource();
        $resource->execute();
        $count = $resource->rowCount();
        if ($count) {
            $this->content = '<p class="alert">'
                           . _s('Deprecated tables exist in the database. Please continue to re-install.')
                           . '</p>'
                           . '<input type="hidden" name="page" value="admin" />'
                           . '<input type="hidden" name="retry" value="1" />'
                           . '<input type="hidden" name="action" value="clear" />';
        } else {
            $this->loadForm();
        }
    }

    protected function loadForm()
    {
        $this->hasForm = true;
        $vars = $this->vars;
        $this->wizard->setPersist('siteconfig', $vars);

        $elementInfo = array(
            'adminmail'     => _s('Admin email'),
            'adminusername' => _s('Admin username'),
            'adminname'     => _s('Admin name'),
            'adminpass'     => _s('Admin password'),
            'adminpass2'    => _s('Confirm password'),
        );
        $displayItem = function ($item) use ($vars, $elementInfo) {
            $content = '<div class="item">'
                     . '<label for="' . $item . '">' . $elementInfo[$item]
                     . '</label><p class="caption"></p>'
                     . '<input type="text" name="' . $item . '" id="'
                     . $item . '" value="' . $vars[$item] . '" />'
                     . '<em id="' . $item . '-status" class="">&nbsp;</em>'
                     . '<p id="' . $item . '-message" class="alert">&nbsp;'
                     . '</p></div>';

            return $content;
        };

        $content = '<div class="install-form well">';
        $content .= '<h3 class="section">' . _s('Administrator account')
                  . '</h3>';
        $content .= $displayItem('adminmail');
        $content .= $displayItem('adminusername');
        $content .= $displayItem('adminname');

        $item = 'adminpass';
        $content .= '<div class="item">'
                  . '<label for="' . $item . '">' . $elementInfo[$item]
                  . '</label><p class="caption"></p>'
                  . '<input type="password" name="' . $item . '" id="'
                  . $item . '" value="' . $vars[$item] . '" />'
                  . '</div>';
        $item = 'adminpass2';
        $content .= '<div class="item">'
                  . '<label for="' . $item . '">' . $elementInfo[$item]
                  . '</label><p class="caption"></p>'
                  . '<input type="password" name="' . $item . '" id="'
                  . $item . '" value="' . $vars[$item] . '" />'
                  . '<em id="adminpass-status" class="">&nbsp;</em>'
                  . '<p id="adminpass-message" class="alert">&nbsp;</p>'
                  . '</div>';
        $content .= '</div>';

        $this->content = $content;

        $this->headContent .=<<<'STYLE'
<style type='text/css' media='screen'>
    .item {
        margin-top: 20px;
    }
    .install-form input[type='password'] {
        width: 400px;
        font-size: 16px;
        color: #666;
    }
</style>
STYLE;

        $this->footContent .=<<<"SCRIPT"
<script type='text/javascript'>
var url='$_SERVER[PHP_SELF]';
$(document).ready(function(){
    $('input[type=text]').each(function(index) {
        check($(this).attr('id'));
        $(this).bind('change', function() {
            update($(this).attr('id'));
        });
    });
    check('adminpass');
    $('#adminpass, #adminpass2').each(function(index) {
        $(this).bind('change', function() {
            $.get(url,
                {'action': 'set', 'var': $(this).attr('name'),
                    'val': this.value, 'page': 'admin'},
                function (data) {
                if (data) {
                    check('adminpass');
                }
            });
        });
    });
});

function update(id) {
    $.get(url, {
        'action': 'set', 'var': id, 'val': $('#' + id).val(), 'page': 'admin'},
        function (data) {
        if (data) {
            check(id);
        }
    });
}

function check(id) {
    $.get(url, {'action': 'check', 'var': id, 'page': 'admin'},
        function (data) {
        if (data.length == 0) {
            $('#'+id+'-status').attr('class', 'success');
            $('#'+id+'-message').css('display', 'none');
        } else {
            $('#'+id+'-status').attr('class', 'failure');
            $('#'+id+'-message').html(data);
            $('#'+id+'-message').css('display', 'block');
        }
    });
}

</script>
SCRIPT;
    }
}
