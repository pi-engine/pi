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

use Locale;

class Presetting extends AbstractController
{
    public function init()
    {
        $this->wizard->destroyPersist();
    }

    public function submitAction()
    {
        $language = $this->request->getParam('language');
        if (!empty($language)) {
            $languageList = $this->getLanguages();
            if (isset($languageList[$language])) {
                $this->wizard->setLocale($language);
                $this->wizard->setCharset($this->getCharset($language));
            }
        }
    }

    public function indexAction()
    {
        $this->loadContent();
    }

    protected function getLanguages()
    {
        $languageList = array();

        $iterator = new \DirectoryIterator($this->wizard->getRoot() . '/locale/');
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }
            $localeName = $fileinfo->getFilename();
            if ($localeName[0] == '.') {
                continue;
            }
            $title = $localeName;
            if (class_exists('\Locale')) {
                $title = Locale::getDisplayName($localeName) ?: $title;
            }
            $iconFile = $fileinfo->getPathname() . '/icon.gif';
            //$meta = include $fileinfo->getPathname() . '/.meta.php';
            $languageList[$localeName] = array(
                'title' => $title,
                'icon'  => $iconFile
            );
        }
        asort($languageList);

        return $languageList;
    }

    protected function getCharset($locale)
    {
        return 'UTF-8';
    }

    protected function loadContent()
    {
        $this->loadLanguageForm();
        $this->loadRequirementForm();
    }

    protected function loadLanguageForm()
    {
        $languageList = $this->getLanguages();

        $content = '
            <h2>' . _t('Language Selection') . '</h2>
            <p class="caption">' . _t('Choose the language for the installation and website') . '</p>
            <div class="install-form">
                <p>
                    <select id="language-selector" size="5" name="language">';
                        foreach ($languageList as $name => $language) {
                            $selected = ($name == $this->wizard->getLocale()) ? " selected='selected'" : "";
                            $content .= sprintf('<option value="%s"%s>%s</option>', $name, $selected, $language['title']);
                        }
                        $content .= '</select>
                </p>
            </div>';
        $this->content .= $content;

        $this->headContent .=<<<'STYLE'
<style type="text/css" media="screen">
    #language-selector {
        width: 300px;
        margin: 10px auto;
        border: 1px solid #ddd;
    }

    #language-selector li {
        margin: 0;
        list-style: none;
        cursor: pointer;
    }

    #language-selector .ui-selecting {
        background: #ccc;
    }

    #language-selector .ui-selected {
        background: #999;
        color: #fff;
    }
</style>
STYLE;

        $this->footContent .=<<<"SCRIPT"
<script type="text/javascript">
$("#language-selector").change(function () {
    $.ajax({
        url: "$_SERVER[PHP_SELF]",
        data: {page: "presetting", language: this.value, action: "submit"},
    });
});
</script>
SCRIPT;

    }

    protected function loadRequirementForm()
    {
        $this->verifyRequirement();
        if ($this->status < 0) {
            $content = '<h2><span class="failure">' . _t('Sever setting detection') . '</span> <a href="javascript:void(0);" id="advanced-label"><span style="display: none;">[+]</span><span>[-]</span></a></h2>';
        } else {
            $content = '<h2><span class="success">' . _t('Sever setting detection') . '</span> <a href="javascript:void(0);" id="advanced-label"><span>[+]</span><span style="display: none;">[-]</span></a></h2>';
        }
        $content .= '
            <p class="caption">' . _t('Check server settings and extensions') . '</p>
            <div class="install-form advanced-form" id="advanced-form">
                <h3 class="section">' . _t('System requirements') . '</h3>
                <p class="caption">' . _t('Server settings and system extensions required by Pi Engine') . '</p>';
                foreach ($this->result['system'] as $item => $result) {
                    $value = $result['value'];
                    $style = 'success';
                    switch ($result['status']) {
                        case -1:
                            $style = 'failure';
                            $value = $value ?: _t('Invalid');
                            break;
                        case 0:
                            $style = 'warning';
                            $value = $value ?: _t('Not desired');
                            break;
                        case 1:
                        default:
                            $style = 'success';
                            $value = $value ?: _t('Valid');
                            break;
                    }
                    $content .= '
                        <p><div class="label">' . $result['title'] . '</div>
                        <div class="text"><span class="' . $style . '">' . $value . '</span>';

                    if (!empty($result['message'])) {
                        $content .= '<em class="message">' . $result['message'] . '</em>';
                    }
                    $content .= '</div></p>';
                }

                $content .= '
                <h3 class="section">' . _t('System extension recommendations') . '</h3>
                <p class="caption">' . _t('Extesions recommended for better functionality or performance') . '</p>';
                foreach ($this->result['extension'] as $item => $result) {
                    $value = $result['value'];
                    $style = 'success';
                    switch ($result['status']) {
                        case -1:
                            $style = 'failure';
                            $value = $value ?: _t('Invalid');
                            break;
                        case 0:
                            $style = 'warning';
                            $value = $value ?: _t('Not desired');
                            break;
                        case 1:
                        default:
                            $style = 'success';
                            $value = $value ?: _t('Valid');
                            break;
                    }
                    $content .= '
                        <p><div class="label">' . $result['title'] . '</div>
                        <div class="text"><span class="' . $style . '">' . $value . '</span>';

                    if (!empty($result['message'])) {
                        $content .= '<span class="message">' . $result['message'] . '</span>';
                    }
                    $content .= '</div></p>';
                }

        $content .= '
            </div>';
        $this->content .= $content;

        $this->footContent .= '
            <script type="text/javascript">
            $(function() {' .
                (($this->status < 0)
                    ? '
                        $("#advanced-form").slideToggle();
                        $("#advanced-label span.toggle-span").toggle();
                    '
                    :''
                ) .
                '$("#advanced-label").click(function() {
                    $("#advanced-form").slideToggle();
                    $("#advanced-label span").toggle();
                });
            })
            </script>';

    }

    protected function verifyRequirement()
    {
        $this->result['system'] = $this->checkSystem();
        $this->result['extension'] = $this->checkExtension();
        foreach ($this->result['system'] as $item => $result) {
            $this->status = min($this->status, $result['status']);
            if ($this->status < 0) {
                break;
            }
        }
        $status = 1;
        foreach ($this->result['extension'] as $item => $result) {
            $status = min($status, $result['status']);
            if ($status < 0) {
                $this->status = -1;
                break;
            }
        }
    }

    protected function checkExtension($item = null)
    {
        if (empty($item)) {
            $result = array();
            foreach ($this->wizard->getConfig('extension') as $key => $item) {
                $res = $this->checkExtension($key);
                $res['title'] = $item['title'];
                $res['message'] = $res['status'] ? '' : $item['message'];
                $result[$key] = $res;
            }

            return $result;
        }

        $value = '';
        $status = extension_loaded($item) ? 1 : 0;
        switch ($item) {
            case 'apc':
                if ($status) {
                    $value = class_exists('APCIterator', false) ? 'APCIterator available' : 'APCIterator unavailable';
                }
                break;
            case 'gd':
                if ($status) {
                    $gdlib = gd_info();
                    $value = $gdlib['GD Version'];
                }
                break;
            case 'curl':
                $status = function_exists('curl_exec') ? 1 : 0;
            default:
                break;
        }

        $result = array(
            'status'    => $status,
            'value'     => $value,
        );
        return $result;
    }

    protected function checkSystem($item = null)
    {
        if (empty($item)) {
            $result = array();
            foreach ($this->wizard->getConfig('system') as $item => $title) {
                $res = $this->checkSystem($item);
                $res['title'] = $title;
                $result[$item] = $res;
            }

            return $result;
        }

        $result = array(
            'status'    => 0,
            'value'     => _t('Unknown'),
            'message'   => '',
        );
        $method = 'checkSystem' . ucfirst($item);
        if (!method_exists($this, $method)) {
            return $result;
        }
        return $this->$method();
    }


    protected function checkSystemServer()
    {
        $status = 1;
        $message = '';
        $value = $_SERVER["SERVER_SOFTWARE"];
        if (stristr($_SERVER["SERVER_SOFTWARE"], 'nginx')) {
            $status = 1;
            $message = _t('Make sure that configurations have been set up correctly for nginx. Refer to <a href="http://nginx.net" title="nginx" target="_blank">nginx</a> and <a href="http://dev.xoopsengine.org" title="Pi Engine" target="_blank">Pi Engine Dev</a> for instructions.');
        } elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'apache')) {
            // A debug was discovered by voltan that apache_get_modules could be not available
            // See: http://php.net/manual/en/function.apache-get-modules.php
            if (function_exists('apache_get_modules')) {
                $modules = apache_get_modules();
                if (!in_array('mod_rewrite', $modules)) {
                    $status = 0;
                }
            } elseif (getenv('HTTP_MOD_REWRITE') != 'On') {
                ob_start();
                phpinfo(INFO_MODULES);
                $contents = ob_get_contents();
                ob_end_clean();
                if (strpos($contents, 'mod_rewrite') === false) {
                    $status = 0;
                }
            }
            if ($status == 0) {
                $message = _t('Apache "mod_rewrite" module is required, however it is not detected. Check <a href="http://httpd.apache.org/docs/current/mod/mod_rewrite.html" title="mod_rewrite" target="_blank">mod_rewrite</a> for details.');
            }
        } else {
            $status = -1;
            $message = _t('The webserver is currently not supported, please use <a href="http://nginx.net" title="nginx" target="_blank">nginx</a> or <a href="http://www.php.net/manual/en/book.apache.php" target="_blank" title="Apache">Apache</a>.');
        }

        $result = array(
            'status'    => $status,
            'value'     => $value,
            'message'   => $message,
        );
        return $result;
    }

    protected function checkSystemPhp()
    {
        $status = 1;
        $value = PHP_VERSION;
        //$value = '5.2';
        $message = '';
        if (version_compare($value, '5.3.0') < 0) {
            $status = -1;
            $message = _t('Version 5.3.0 or higher is required.');
        }

        $result = array(
            'status'    => $status,
            'value'     => $value,
            'message'   => $message,
        );
        return $result;
    }

    protected function checkSystemPdo()
    {
        $status = 1;
        $value = '';
        $message = '';
        if (!extension_loaded('pdo')) {
            $status = 0;
        }
        $drivers = \PDO::getAvailableDrivers();
        $value = implode(', ', $drivers);
        if (empty($drivers) || !in_array('mysql', $drivers)) {
            $status = 0;
        }
        if (!$status) {
            $message = _t('PHP Data Objects (PDO) extension with MySQL driver is required for regular Pi Engine instances, check <a href="http://www.php.net/manual/en/book.pdo.php" title="PDO" target="_blank">PDO manual</a> for details.');
        }

        $result = array(
            'status'    => $status,
            'value'     => $value,
            'message'   => $message,
        );
        return $result;
    }

    protected function checkSystemPersist()
    {
        $status = 1;
        $value = '';
        $message = '';
        $items = array();
        $persistList = array('apc', 'redis', 'memcached', 'memcache');
        foreach($persistList as $item) {
            if (extension_loaded($item)) {
                $items[] = $item;
            }
        }
        if (!empty($items)) {
            $value = implode(', ', $items);
        } else {
            $status = 0;
            $message = sprintf(_t('There is no recommended persist engine available. One of the following extensions is recommended: %s'), implode(', ', $persistList));
        }

        $result = array(
            'status'    => $status,
            'value'     => $value,
            'message'   => $message,
        );
        return $result;
    }
}
