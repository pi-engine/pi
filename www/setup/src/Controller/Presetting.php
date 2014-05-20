<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Setup\Controller;

/**
 * Presetting controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Presetting extends AbstractController
{
    public function init()
    {
        //$this->wizard->destroyPersist();
    }

    public function submitAction()
    {
        $language = $this->request->getParam('language');
        if (!empty($language)) {
            $languageList = $this->wizard->getLanguages();
            if (isset($languageList[$language])) {
                $this->wizard->setLocale($language);
                $this->wizard->setCharset($this->getCharset($language));
            }
        }
    }

    public function indexAction()
    {
        // Destroy persistent data but keep language and charset
        $language = $this->wizard->getLocale();
        $charset = $this->wizard->getCharset();
        $this->wizard->destroyPersist();
        $this->wizard->setLocale($language);
        $this->wizard->setCharset($charset);

        $this->loadContent();
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
        $locale = $this->wizard->getLocale();
        $languageList = $this->wizard->getLanguages();

        $listPattern =<<<EOT
<li class="list-group-item language-picker">
    <input type="radio" name="language" value="%s"%s>
    <img src="%s" alt="%s" title="%s" style="padding: 0 5px;" />
     %s
</li>
EOT;

        $languageString = '<ul class="list-group">';
        foreach ($languageList as $name => $language) {
            $languageString .= sprintf(
                $listPattern,
                $name,
                $name == $locale ? ' checked' : '',
                $language['icon'],
                $language['title'],
                $language['title'],
                $language['title']
            );
        }
        $languageString .= '</ul>';

        $title      = _s('Language Selection');
        $caption    = _s('Choose the language for the installation and website.');
        $groupPattern =<<<EOT
<div class="well">
    <h2>%s</h2>
    <p class="caption">%s</p>
    <div class="install-form">%s</div>
</div>
EOT;
        $content = sprintf(
            $groupPattern,
            $title,
            $caption,
            $languageString
        );
        $this->content .= $content;

        $footContent =<<<SCRIPT
<script>
$(document).ready(function() {
    $('input[type=radio][name=language]').change(function() {
        $.ajax({
            url: "%s",
            data: { page: "presetting", language: this.value, action: "submit" },
            success: function (data) { window.location.reload(true); }
        });
    });
});
</script>
SCRIPT;
        $this->footContent .= sprintf($footContent, $_SERVER['PHP_SELF']);

    }

    protected function loadRequirementForm()
    {
        $this->verifyRequirement();
        $title = _s('Sever setting detection');
        if ($this->status < 0) {
            $content = '<h2><span class="failure">'
                     . $title
                     . '</span> <a href="javascript:void(0);"'
                     . ' id="advanced-label">'
                     . '<span style="display: none;">[+]</span>'
                     . '<span>[-]</span></a></h2>';
        } else {
            $content = '<h2><span class="success">'
                     . $title
                     . '</span> <a href="javascript:void(0);"'
                     . ' id="advanced-label">'
                     . '<span>[+]</span><span style="display: none;">'
                     . '[-]</span></a></h2>';
        }
        $caption = _s('Validate server settings and extensions');
        $content .= '<p class="caption">'
                  . $caption
                  . '</p><div class="install-form advanced-form well"'
                  . ' id="advanced-form"><h3 class="section">'
                  . _s('System requirements')
                  . '</h3><p class="caption">'
                  . _s('Server settings and system extensions required by Pi Engine')
                  . '</p>';
        foreach ($this->result['system'] as $item => $result) {
            $value = $result['value'];
            switch ($result['status']) {
                case -1:
                    $style = 'failure';
                    $value = $value ?: _s('Invalid');
                    break;
                case 0:
                    $style = 'warning';
                    $value = $value ?: _s('Not available');
                    break;
                case 1:
                default:
                    $style = 'success';
                    $value = $value ?: _s('Valid');
                    break;
            }
            $content .= '<p><div class="label">' . $result['title'] . '</div>'
                      . '<div class="text"><span class="' . $style . '">'
                      . $value . '</span>';

            if (!empty($result['message'])) {
                $content .= '<em class="message">' . $result['message']
                          . '</em>';
            }
            $content .= '</div></p>';
        }

        $content .= '<h3 class="section">'
                  . _s('System extension recommendations')
                  . '</h3><p class="caption">'
                  . _s('Extensions recommended for better functionality or performance')
                  . '</p>';
        foreach ($this->result['extension'] as $item => $result) {
            $value = $result['value'];
            switch ($result['status']) {
                case -1:
                    $style = 'failure';
                    $value = $value ?: _s('Invalid');
                    break;
                case 0:
                    $style = 'warning';
                    $value = $value ?: _s('Not available');
                    break;
                case 1:
                default:
                    $style = 'success';
                    $value = $value ?: _s('Valid');
                    break;
            }
            $content .= '<p><div class="label">' . $result['title'] . '</div>'
                      . '<div class="text"><span class="' . $style . '">'
                      . $value . '</span>';

            if (!empty($result['message'])) {
                $content .= '<span class="message">' . $result['message']
                          . '</span>';
            }
            $content .= '</div></p>';
        }

        $content .= '</div>';
        $this->content .= $content;

        $this->footContent .= '<script>' . PHP_EOL . '$(function() {';
        if ($this->status < 0) {
            $this->footContent .= '
                $("#advanced-form").slideToggle();
                $("#advanced-label span.toggle-span").toggle();
                ';
        }
        $this->footContent .= '
            $("#advanced-label").click(function() {
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
                    $value = class_exists('APCIterator', false)
                        ? 'APCIterator available' : 'APCIterator unavailable';
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
            'value'     => _s('Unknown'),
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
            $message = _s('Make sure that configurations have been set up correctly for nginx. Refer to <a href="http://nginx.net" title="nginx" target="_blank">nginx</a> and <a href="http://dev.xoopsengine.org" title="Pi Engine" target="_blank">Pi Engine Dev</a> for instructions.');
        } elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'apache')) {
            // A debug was discovered by voltan that
            // apache_get_modules could be not available
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
                // The status should not be set to -1 directly
                // since mod_rewrite is not detected for some environments,
                // for instance if PHP runs with cgi mode
                if (strpos($contents, 'mod_rewrite') === false) {
                    $status = 0;
                }
            }
            if ($status == 0) {
                $message = _s('Apache "mod_rewrite" module is required, however it is not detected. Check <a href="http://httpd.apache.org/docs/current/mod/mod_rewrite.html" title="mod_rewrite" target="_blank">mod_rewrite</a> for details.');
            }
        } else {
            $status = -1;
            $message = _s('The webserver is currently not supported, please use <a href="http://nginx.net" title="nginx" target="_blank">nginx</a> or <a href="http://www.php.net/manual/en/book.apache.php" target="_blank" title="Apache">Apache</a>.');
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
        $message = '';
        if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50400) {
            $status = -1;
            $message = _s('Version 5.4.0 or higher is required.');
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
            $message = _s('PHP Data Objects (PDO) extension with MySQL driver is required for regular Pi Engine instances, check <a href="http://www.php.net/manual/en/book.pdo.php" title="PDO" target="_blank">PDO manual</a> for details.');
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
            $message = sprintf(
                _s('There is no recommended persist engine available. One of the following extensions is recommended: %s'),
                implode(', ', $persistList)
            );
        }

        $result = array(
            'status'    => $status,
            'value'     => $value,
            'message'   => $message,
        );

        return $result;
    }
}
