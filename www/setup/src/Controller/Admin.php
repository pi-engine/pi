<?php
/**
 * Pi Engine Setup Controller
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
 * @package         Pi\Setup
 * @version         $Id$
 */

namespace Pi\Setup\Controller;

use Pi;
use Pi\Application\Installer\Module as ModuleInstaller;
use Pi\Acl\Acl;

class Admin extends AbstractController
{
    protected $hasBootstrap = true;

    public function init()
    {
        /*
        $locale = $this->wizard->getLocale();
        $charset = $this->wizard->getCharset();
        Pi::config()->set('locale', $locale);
        Pi::config()->set('charset', $charset);
        */
        $db = Pi::service('database')->db();
        Pi::registry('db', $db);

        $vars = $this->wizard->getPersist('siteconfig');
        if (empty($vars)) {
            $vars['adminname'] = 'root';
            $hostname = preg_replace('/^www\./i', '', $_SERVER['SERVER_NAME']);
            if (false === strpos($hostname, '.')) {
                $hostname .= '.com';
            }
            $vars['adminmail'] = $vars['adminname'] . '@' . $hostname;
            $vars['adminpass'] = $vars['adminpass2'] = '';
            $this->wizard->setPersist('siteconfig', $vars);
        }
        $this->vars = $vars;
    }

    public function clearAction()
    {
        $this->hasForm = true;
        if ($this->request->getPost('retry')) {
            $adapter = Pi::db()->adapter();
            $tablePrefix = Pi::db()->getTablePrefix();

            try {
                // Drop all tables
                $sql = sprintf('SHOW TABLES LIKE %s', $adapter->getPlatform()->quoteValue($tablePrefix . '%%'));
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
                $this->content = '<p class="error">' . _t('System module uninstallation is failed. Please continue to try again.') . '</p>' .
                        $e->getMessage() .
                        '<input type="hidden" name="page" value="admin" />' .
                        '<input type="hidden" name="retry" value="1" />' .
                        '<input type="hidden" name="action" value="clear" />';
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
                    $error = _t('Information is required.');
                }
                break;
            case 'adminmail':
                if (empty($val)) {
                    $error = _t('Information is required.');
                } elseif (!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i', $val)) {
                    $error = _t('Invalid Email.');
                }
                break;
            case 'adminpass':
            case 'adminpass2':
                $v1 = $this->vars['adminpass'];
                $v2 = $this->vars['adminpass2'];
                if (empty($v1) || empty($v2)) {
                    $error = _t('Information is required.');
                } elseif ($v1 !== $v2) {
                    $error = _t('The two passwords do not match');
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
            $this->content = '<p class=\'error\'>' . _t('System module installation is failed. Please continue to try again.') . '</p>' .
                        $installer->renderMessage() .
                        '<input type=\'hidden\' name=\'page\' value=\'admin\' />' .
                        '<input type=\'hidden\' name=\'retry\' value=\'1\' />' .
                        '<input type=\'hidden\' name=\'action\' value=\'clear\' />';
            return;
        }

        $vars = $this->vars;
        $vars['adminname'] = $this->request->getPost('adminname');
        $vars['adminmail'] = $this->request->getPost('adminmail');
        $vars['adminpass'] = $this->request->getPost('adminpass');
        $vars['adminpass2'] = $this->request->getPost('adminpass2');
        $this->wizard->setPersist('siteconfig', $vars);

        $error = array();
        if (empty($vars['adminname'])) {
            $error['name'][] = _t('Username is required.');
        }
        if (empty($vars['adminmail'])) {
            $error['email'][] = _t('Email is required.');
        }
        if (empty($vars['adminpass'])) {
            $error['pass'][] = _t('Password is required.');
        }
        if (!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i', $vars['adminmail'])) {
            $error['email'][] = _t('Invalid Email.');
        }
        if ($vars['adminpass'] != $vars['adminpass2']) {
            $error['pass'][] = _t('The two passwords do not match');
        }
        if (!$error) {
            // Update global contact email
            $configModel = Pi::model('config');
            $configModel->update(array('value' => $vars['adminmail']), array('name' => 'adminmail'));

            // Create root admin user
            $userData = array(
                'identity'      => $vars['adminname'],
                'credential'    => $vars['adminpass'],
                'email'         => $vars['adminmail'],
                'active'        => 1,
                'role'          => Acl::MEMBER,
                'role_staff'    => Acl::ADMIN,
            );
            $result = Pi::service('api')->system(array('member', 'add'), $userData);
            $this->status = $result['status'];

            // Create system accounts
            $hostname = preg_replace('/^www\./i', '', $_SERVER['SERVER_NAME']);
            $accounts = array(
                'manager'   => array(
                    'name'          => __('Manager'),
                    'role_staff'    => 'manager',
                ),
                'moderator'   => array(
                    'name'          => __('Moderator'),
                    'role_staff'    => 'moderator',
                ),
                'editor'   => array(
                    'name'          => __('Editor'),
                    'role_staff'    => 'editor',
                ),
                'staff'   => array(
                    'name'          => __('Staff'),
                    'role_staff'    => 'staff',
                ),
                'member'   => array(
                    'name'          => __('Member'),
                    'role_staff'    => '',
                ),
            );
            foreach ($accounts as $identity => $data) {
                $data['identity']   = $identity;
                $data['email']      = $identity . '@' . $hostname;
                $data = array_merge($userData, $data);
                Pi::service('api')->system(array('member', 'add'), $data);
            }
        }

        if ($this->status < 1) {
            $this->loadForm();
        }
    }

    public function indexAction()
    {
        $this->hasForm = true;

        $adapter = Pi::db()->adapter();
        $tablePrefix = Pi::db()->getTablePrefix();
        $sql = sprintf('SHOW TABLES LIKE %s', $adapter->getPlatform()->quoteValue($tablePrefix . '%'));
        $resource = $adapter->query($sql)->getResource();
        $resource->execute();
        $count = $resource->rowCount();
        if ($count) {
            $this->content = '<p class=\'error\'>' . _t('Deprected tables exist in the database. Please continue to re-install.') . '</p>' .
                        '<input type=\'hidden\' name=\'page\' value=\'admin\' />' .
                        '<input type=\'hidden\' name=\'retry\' value=\'1\' />' .
                        '<input type=\'hidden\' name=\'action\' value=\'clear\' />';
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
            'adminmail' => _t('Admin email'),
            'adminname' => _t('Admin username'),
            'adminpass' => _t('Admin password'),
            'adminpass2' => _t('Confirm password'),
        );
        $displayItem = function ($item) use ($vars, $elementInfo) {
            $content = '<div class=\'item\'>
                <label for=\'' . $item . '\'>' . $elementInfo[$item] . '</label>
                <p class=\'capthion\'></p>
                <input type=\'text\' name=\'' . $item . '\' id=\'' . $item . '\' value=\'' . $vars[$item] . '\' />
                <em id=\'' . $item . '-status\' class=\'\'>&nbsp;</em>
                <p id=\'' . $item . '-message\' class=\'admin-message\'>&nbsp;</p>
                </div>';
            return $content;
        };

        $content = '<div class=\'install-form\'>';
        $content .= '<h3 class=\'section\'>' . _t('Administrator account') . '</h3>';
        $content .= $displayItem('adminmail');
        $content .= $displayItem('adminname');

        $item = 'adminpass';
        $content .= '<div class=\'item\'>
            <label for=\'' . $item . '\'>' . $elementInfo[$item] . '</label>
            <p class=\'capthion\'></p>
            <input type=\'password\' name=\'' . $item . '\' id=\'' . $item . '\' value=\'' . $vars[$item] . '\' />
            </div>';
        $item = 'adminpass2';
        $content .= '<div class=\'item\'>
            <label for=\'' . $item . '\'>' . $elementInfo[$item] . '</label>
            <p class=\'capthion\'></p>
            <input type=\'password\' name=\'' . $item . '\' id=\'' . $item . '\' value=\'' . $vars[$item] . '\' />
            <em id=\'adminpass-status\' class=\'\'>&nbsp;</em>
            <p id=\'adminpass-message\' class=\'admin-message\'>&nbsp;</p>
            </div>';
        $content .= '</div>';

        $this->content = $content;

        $this->headContent .=<<<'STYLE'
<style type='text/css' media='screen'>
    .admin-message {
        display: none;
        font-size: 80%;
        background-color: yellow;
        border: 1px solid #666;
        margin-top: 5px;
        padding-left: 5px;
    }
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
            $.get(url, {'action': 'set', 'var': $(this).attr('name'), 'val': this.value, 'page': 'admin'}, function (data) {
                if (data) {
                    check('adminpass');
                }
            });
        });
    });
});

function update(id) {
    $.get(url, {'action': 'set', 'var': id, 'val': $('#' + id).val(), 'page': 'admin'}, function (data) {
        if (data) {
            check(id);
        }
    });
}

function check(id) {
    $.get(url, {'action': 'check', 'var': id, 'page': 'admin'}, function (data) {
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
