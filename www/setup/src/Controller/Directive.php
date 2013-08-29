<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Setup\Controller;

use Pi\Setup\Host;

/**
 * Directive controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Directive extends AbstractController
{
    const DIR_MODULE    = 'module';
    const DIR_THEME     = 'theme';
    const DIR_CONFIG    = 'config';
    const DIR_CUSTOM    = 'custom';
    const DIR_CACHE     = 'cache';
    const DIR_LOG       = 'log';
    const DIR_VENDOR    = 'vendor';

    protected $host;

    public function init()
    {
        @set_time_limit(0);
    }

    protected function loadForm()
    {
        $this->hasForm = true;
        $this->loadHostForm();
        $this->loadPersistForm();
    }

    /**
     * Normalize specified paths
     * @param array $vars
     */
    protected function normalizeHost(&$vars)
    {
        $vars['module']['path'] = !empty($vars['module']['path'])
            ? $vars['module']['path']
            : $vars['usr']['path'] . '/' . static::DIR_MODULE;
        $vars['theme']['path']  = !empty($vars['theme']['path'])
            ? $vars['theme']['path']
            : $vars['usr']['path'] . '/' . static::DIR_THEME;
        $vars['config']['path'] = !empty($vars['config']['path'])
            ? $vars['config']['path']
            : $vars['var']['path'] . '/' . static::DIR_CONFIG;
        $vars['custom']['path'] = !empty($vars['custom']['path'])
            ? $vars['custom']['path']
            : $vars['var']['path'] . '/' . static::DIR_CUSTOM;
        $vars['cache']['path']  = !empty($vars['cache']['path'])
            ? $vars['cache']['path']
            : $vars['var']['path'] . '/' . static::DIR_CACHE;
        $vars['log']['path']    = !empty($vars['log']['path'])
            ? $vars['log']['path']
            : $vars['var']['path'] . '/' . static::DIR_LOG;
        $vars['vendor']['path'] = !empty($vars['vendor']['path'])
            ? $vars['vendor']['path']
            : $vars['lib']['path'] . '/' . static::DIR_VENDOR;
    }

    public function indexAction()
    {
        $this->loadForm();
    }

    public function persistAction()
    {
        $persist = $this->request->getParam('persist');
        $this->wizard->setPersist('persist', $persist);

        echo '1';
    }

    /**
     * Checks if a path/URL exists
     */
    public function pathAction()
    {
        $this->host = new Host($this->wizard);
        $this->host->init();

        $path = $this->request->getParam('var');
        $val = htmlspecialchars(trim($this->request->getParam('path')));
        $this->host->setPath($path, $val);
        list($type, $key) = explode('_', $path, 2);
        if ($type == 'url') {
            $status = $this->host->checkUrl($key);
        } else {
            $status = $this->host->checkPath($key);
        }
        echo $status;

        return;
    }

    /**
     * Checks if permissions for a path are set properly
     */
    public function messageAction()
    {
        $this->host = new Host($this->wizard);
        $this->host->init();

        $path = $this->request->getParam('var');
        $val = htmlspecialchars(trim($this->request->getParam('path')));
        $this->host->setPath($path, $val);
        list($type, $key) = explode('_', $path, 2);
        if ($type == 'path') {
            $messages = $this->host->checkSub($key);
        }

        $messageString = '';
        if (!empty($messages)) {
            $messageString = '<ul>';
            foreach (array_keys($messages) as $key) {
                $messageString .= '<li>' . sprintf(
                    _s('%s is NOT writable.'),
                    $key
                ) . '</li>';
            }
            $messageString .= '</ul>';
        }

        echo $messageString;
    }

    /**
     * Accepts post data upon submission and creates host.php, engine.php
     */
    public function submitAction()
    {
        $wizard = $this->wizard;
        $this->host = new Host($wizard);
        $this->host->init();
        $errorsSave = array();
        $errorsConfig = array();
        $configs = array();

        $vars = $wizard->getPersist('paths');
        $this->normalizeHost($vars);
        $wizard->setPersist('paths', $vars);

        /**#@+
         * config/host.php
         */
        $file = $vars['config']['path'] . '/host.php';
        $file_dist = $wizard->getRoot() . '/dist/host.php.dist';
        $content = file_get_contents($file_dist);
        foreach ($vars as $var => $val) {
            if (!empty($val['path'])) {
                $content = str_replace(
                    '%' . $var . '_path%',
                    $val['path'],
                    $content
                );
            }
            if (!empty($val['url'])) {
                $content = str_replace(
                    '%' . $var . '_uri%',
                    $val['url'],
                    $content
                );
            }
        }
        $configs[] = array('file' => $file, 'content' => $content);
        /**#@-*/

        /**#@+
         * config/engine.php
         */
        // List of configs
        $config = array(
            'identifier'    => 'pi' . substr(md5($vars['www']['url']), 0, 4),
            'salt'          => md5(uniqid(mt_rand(), true)),
            'storage'       => $wizard->getPersist('persist'),
            'namespace'     => substr(md5($vars['www']['url']), 0, 4),
            'environment'   => 'development',
        );
        $file = $vars['config']['path'] . '/engine.php';
        $file_dist = $wizard->getRoot() . '/dist/engine.php.dist';
        $content = file_get_contents($file_dist);
        foreach ($config as $var => $val) {
            $content = str_replace('%' . $var . '%', $val, $content);
        }
        $configs[] = array('file' => $file, 'content' => $content);
        /**#@-*/

        // Write content to files and record errors in case occured
        foreach ($configs as $config) {
            $error = false;
            if (!$file = fopen($config['file'], 'w')) {
                $error = true;
            } else {
                if (1 > fwrite($file, $config['content'])) {
                    $error = true;
                }
                fclose($file);
            }
            if ($error) {
                $errorsSave[] = $config;
            }
        }

        // Build resource configuration files
        if (empty($errorsSave)) {
            // Prepare for config files from dist files
            $iterator = new \DirectoryIterator($vars['config']['path']);
            foreach ($iterator as $fileinfo) {
                if (!$fileinfo->isFile()) {
                    continue;
                }
                $filename = $fileinfo->getPathname();
                $suffix = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                // Skip the file if its suffix is not '.dist'
                if ('dist' !== $suffix) {
                    continue;
                }
                $target = substr($filename, 0, -5);
                if (file_exists($target) && !is_writable($target)) {
                    chmod($target, 0777);
                }
                $status = copy($filename, $target);
                if (!$status || !is_readable($target)) {
                    $errorsConfig[] = $target;
                }
            }

            if (empty($errorsConfig)) {
                $this->status = 1;
            }
        }

        $content = '';
        // Display saving error messages
        if (!empty($errorsSave)) {
            $content .= '<h3>' . _s('Configuration file write error')
                      . '</h3>';
            foreach ($errorsSave as $error) {
                $content .= '<p class="caption" style="margin-top: 10px;">'
                          . sprintf(
                              _s('The configuration file "%s" is not written correctly.'),
                              $error['file']
                            )
                          . '</p>'
                          . '<textarea cols="80" rows="10" class="span12">'
                          . $error['content']
                          . '</textarea>';
            }
        // Display config file error messages
        } elseif (!empty($errorsConfig)) {
            $content .= '<h3>' . _s('Configuration file copy error') . '</h3>'
                      . '<p class="caption">'
                      . _s('The configuration files are not copied correctly or not readable, please create and/or set read permissions for the files manually.')
                      . '</p>'
                      . '<div class="message alert">'
                      . '<ul>';
            foreach ($errorsConfig as $file) {
                $content .= '<li>' . $file . '</li>';
            }
            $content .= '</ul></div>';
        }
        $this->content .= $content;
    }

    protected function loadPersistForm()
    {
        $persist = $this->wizard->getPersist('persist');
        $config = $this->wizard->getConfig('extension');
        $content = '';

        $valid = false;
        if (extension_loaded('apc')) {
            $persist = $persist ?: 'apc';
            $valid = true;
            $checkedString = ($persist == 'apc') ? 'checked' : '';
        } else {
            $checkedString = 'disabled';
        }
        $content .= '<label class="radio"><input type="radio" name="persist"'
                  . ' value="apc" ' . $checkedString . ' />'
                  . $config['apc']['title'] . '</label>'
                  . '<p class="caption">' . $config['apc']['message']
                  . '</p>';

        if (extension_loaded('redis')) {
            $persist = $persist ?: 'redis';
            $valid = true;
            $checkedString = ($persist == 'redis') ? 'checked' : '';
            $content .= '<label class="radio"><input type="radio" name="persist"'
                      . ' value="redis" ' . $checkedString . ' />'
                      . $config['redis']['title'] . '</label>'
                      . '<p class="caption">' . $config['redis']['message']
                      . '</p>';
        }

        if (extension_loaded('memcached')) {
            $persist = $persist ?: 'memcached';
            $checkedString = ($persist == 'memcached') ? 'checked' : '';
            $valid = true;
        } else {
            $checkedString = ' disabled';
        }
        $content .= '<label class="radio"><input type="radio" name="persist"'
                  . ' value="memcached" ' . $checkedString . ' />'
                  . $config['memcached']['title'] . '</label>'
                  . '<p class="caption">' . $config['memcached']['message']
                  . '</p>';

        if (extension_loaded('memcache')) {
            $persist = $persist ?: 'memcache';
            $checkedString = ($persist == 'memcache') ? 'checked' : '';
            $valid = true;
        } else {
            $checkedString = ' disabled';
        }
        $content .= '<label class="radio"><input type="radio" name="persist"'
                  . ' value="memcache" ' . $checkedString . ' />'
                  . $config['memcache']['title'] . '</label>'
                  . '<p class="caption">' . $config['memcache']['message']
                  . '</p>';

        $checkedString = ($persist == 'filesystem') ? 'checked' : '';
        $content .= '<label class="radio"><input type="radio" name="persist"'
                  . ' value="filesystem" ' . $checkedString . ' />'
                  . _s('File system') . '</label>'
                  . '<p class="caption">'
                  . _s('Caching storage with files on disks specified in following steps.')
                  . '</p></div>';

        $content = '<h2> <span class="success">'
                 . _s('Persistent data container')
                 . '</span> <a href="javascript:void(0);" id="persist-label">'
                 . '<span>[+]</span><span style="display: none;">[-]</span></a>'
                 . '</h2><p class="caption">'
                 . _s('Choose the proper backend container for persistent data')
                 . '</p><div class="install-form advanced-form well"'
                 . ' id="advanced-persist">' . $content . '</div>';

        $this->content .= $content;

        $this->footContent .=<<<"SCRIPT"
<script type='text/javascript'>
$('input[name=persist]').click(function() {
    $.ajax({
        url: '$_SERVER[PHP_SELF]',
        data: {page: 'directive', persist: $(this).val(), action: 'persist'},
    });
});

$('#persist-label').click(function() {
    $('#advanced-persist').slideToggle();
    $('#persist-label span').toggle();
});
</script>
SCRIPT;

        $persist = $persist ?: 'filesystem';
        $this->wizard->setPersist('persist', $persist);
    }

    /**
     * Creates host form
     *
     * There are two parts:
     *
     *  + basic - root path and URI;
     *  + advanced - specify custom paths and URIs
     */
    protected function loadHostForm()
    {
        $this->host = new Host($this->wizard);
        $this->host->init(true);

        // Title and description for each item
        $pathInfo = array(
            'path_www'  => array(
                _s('Documents root physical path'),
                _s('Physical path to the documents (served) directory without trailing slash; PHP executable.'),
            ),
            'url_www'   => array(
                _s('Website location (URL)'),
                _s('Main URL that will be used to access your Pi Engine'),
            ),
            'path_asset'    => array(
                _s('Asset file directory'),
                _s('Physical path to asset file directory without trailing slash.'),
            ),
            'url_asset'     => array(
                _s('URL of asset file root directory'),
                _s('URL that will be used to access asset files.'),
            ),
            'path_upload'   => array(
                _s('Upload directory'),
                _s('Physical path to upload directory without trailing slash. A relative path will be allocated in PI root directory; PHP disabled.'),
            ),
            'url_upload'    => array(
                _s('URL of upload root'),
                _s('URL that will be used to access upload directory. PI root URL will be prepended if relative URL is used.'),
            ),
            'path_static'   => array(
                _s('Static file directory'),
                _s('Physical path to static file directory without trailing slash; PHP disabled.'),
            ),
            'url_static'    => array(
                _s('URL of static file root directory'),
                _s('URL that will be used to access static files. Upload URL will be used if static directory is not set explicitly.'),
            ),
            'path_lib'      => array(
                _s('Library directory'),
                _s('Physical path to library directory without trailing slash. Locate the folder out of web directory to make it secure; PHP executable'),
            ),
            'path_var'      => array(
                _s('Data file directory'),
                _s('Physical path to the data files (writable) directory WITHOUT trailing slash. Locate the folder out of web directory to make it secure; PHP executable'),
            ),
            'path_usr'      => array(
                _s('Root directory for user applications'),
                _s('Physical path to user contributed directory WITHOUT trailing slash.. Locate the folder out of web directory to make it secure; PHP executable'),
            ),
        );

        $controller = $this->host;
        // Anonymous function to create form elements for an item
        $displayItem = function ($item) use ($controller, $pathInfo) {
            $content =<<<"HTML"
<div class='item'>
    <label for='$item'>{$pathInfo[$item][0]}</label>
    <p class='caption'>{$pathInfo[$item][1]}</p>
    <input type='text' name='$item' id='$item'
        value='{$controller->getPath($item)}' />
    <em id='{$item}-status' class='loading'>&nbsp;</em>
    <p id='{$item}-message' class='alert'>&nbsp;</p>
    </div>
HTML;

            return $content;
        };

        $status = $statusBasic = $statusAdvanced = '';
        $content = '';

        // pth of www
        $itemList = array('path_www');
        foreach ($itemList as $item) {
            $content .= $displayItem($item);
        }
        // URI of www
        $item = 'url_www';
        $content .=<<<"HTML"
<div class='item'>
    <label for='$item'>{$pathInfo[$item][0]}</label>
    <p class='caption'>{$pathInfo[$item][1]}</p>
    <input type='text' name='$item' id='$item'
        value='{$controller->getPath($item)}' />
    </div>
HTML;

        // Assemble basic section which is composed of www path and URI
        $contentBasic = '<h3 class="section"><span id="path-basic-label"'
                     . ' class="' . $statusBasic . '">'
                     . _s('Basic settings')
                     . '</span><a href="javascript:void(0);"'
                     . ' id="path-basic-toggle"><span>[+]</span>'
                     . '<span style="display: none;">[-]</span></a></h3>'
                     . '<p class="caption">'
                     . _s('Settings required by system')
                     . '</p>'
                     . '<div class="install-form advanced-form item-container'
                     . ' well" id="path-basic">' . $content . '</div>';

        $content = '';
        // Advanced item elements
        $itemList = array(
            'path_lib', 'path_var', 'path_usr', 'path_asset',
            'url_asset', 'path_upload', 'url_upload', 'path_static',
            'url_static'
        );
        foreach ($itemList as $item) {
            $content .= $displayItem($item);
        }

        // Assemble advanced section by including the advanced items
        $contentAdvanced = '<h3 class="section">'
                         . '<span id="path-advanced-label" class="'
                         . $statusAdvanced
                         . '">' . _s('Advanced settings') . '</span>'
                         . '<a href="javascript:void(0);"'
                         . ' id="path-advanced-toggle">'
                         . '<span>[+]</span><span style="display: none;">[-]'
                         . '</span></a></h3><p class="caption">'
                         . _s('Settings that can help improve security, deployment flexibility, etc. If you are unsure about it, leave as it is.')
                         . '</p>'
                         . '<div class="install-form advanced-form'
                         . ' item-container well" id="path-advanced">'
                         . $content . '</div>';

        // Assemble content by combining basic and advanced sections
        $content = '<h2><span id="paths-label" class="' . $status . '">'
                 . _s('Path settings') . '</span>'
                 . '<a href="javascript:void(0);" id="paths-toggle">'
                 . '<span>[+]</span><span style="display: none;">[-]</span>'
                 . '</a></h2><p class="caption">'
                 . _s('Path and URL settings')
                 . '</p>'
                 . '<div class="install-form advanced-form item-container"'
                 . ' id="paths">' . $contentBasic . $contentAdvanced
                 . '</div>';

        $this->content .= $content;

        // Add cascade style sheet and JavaScript to HTML head
        $this->headContent .=<<<"SCRIPT"
<style type='text/css' media='screen'>
    #paths .item {
        margin-top: 20px;
    }
    #paths p.caption, #paths label {
        margin: 0px;
    }
</style>

<script type='text/javascript'>
function update(id) {
    verifyPath(id);
    checkPath(id);
}

// Validate element value by removing trailing spaces
function verifyPath(id) {
    var val = $('#'+id).val();
    val = val.replace(/([\/\s]*$)/g, '');
    $('#'+id).val(val);
}

// Check if path or URI of an element is valid,
//display status icon and show warning messages if the path or URI is not valid
function checkPath(id) {
    var val = $('#'+id).val();
    var isPath = (id.substr(0, 4) == 'url_') ? 0 : 1;
    if (isPath) {
        // convert to full path
        if (!pathIsAbsolute(val) && id != 'path_www') {
            val = $('#path_www').val() + '/' + val;
        }
    } else {
        // convert to full URI
        if (!urlIsAbsolute(val) && id != 'url_www') {
            val = $('#url_www').val() + '/' + val;
        }
    }

    var url='$_SERVER[PHP_SELF]';
    // Display messages
    $.get(url,
        {'action': 'message', 'var': id, 'path': val, 'page': 'directive'},
        function (data) {
        if (data.length == 0) {
            $('#'+id+'-message').css('display', 'none');
        } else {
            $('#'+id+'-message').html(data);
            $('#'+id+'-message').css('display', 'block');
            triggerParents(id);
        }
    });

    // Display proper status icon
    $.get(url, {'action': 'path', 'var': id, 'path': val, 'page': 'directive'},
        function (data) {
        var statusClass = 'warning';
        if (data == 1) {
            statusClass = 'success';
        }
        if (data == -1) {
            statusClass = 'failure';
            triggerParents(id);
        }
        $('#'+id+'-status').attr('class', statusClass);
    });
}

// Change parent element status in case necessary
function triggerParents(id) {
    $('#' + id).parents('.item-container').each(function(index) {
        $(this).slideDown();
        $('#' + $(this).attr('id') + '-toggle span').css('display', 'none').next().css('display', 'inline');
    });
}

// Check if a path is absolute
function pathIsAbsolute(path) {
    if (/^[a-z]:[\\\/]/i.test(path)) return true;
    if (path.indexOf('\\\\') == 0)   return true;
    if (path.indexOf('/') == 0)   return true;
    return false;
}

// Check if a URI is full URI
function urlIsAbsolute(path) {
    if (/^http(s?):\/\//i.test(path)) return true;
    return false;
}

</script>
SCRIPT;

        // Add JavaScript to bottom of HTML content
        $this->footContent .=<<<'SCRIPT'
<script type='text/javascript'>
$(document).ready(function(){
    // Check if path available, URI accessible
    $('#paths input[type=text][name!=url_www]').each(function(index) {
        checkPath($(this).attr('id'));
        $(this).bind('change', function() {
            update($(this).attr('id'));
        });
    });
    $('#paths input[name=url_www]').change(function() {
        verifyPath($(this).attr('id'));
    });
});

$('#paths-toggle').click(function() {
    $('#paths').slideToggle();
    $('#paths-toggle span').toggle();
});
$('#path-basic-toggle').click(function() {
    $('#path-basic').slideToggle();
    $('#path-basic-toggle span').toggle();
});
$('#path-advanced-toggle').click(function() {
    $('#path-advanced').slideToggle();
    $('#path-advanced-toggle span').toggle();
});
</script>
SCRIPT;
    }
}
