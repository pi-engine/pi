<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Setup\Controller;

use PDO;
use Pi;

/**
 * Database controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Database extends AbstractController
{
    protected $vars;
    protected $dbLink;
    protected $hasBootstrap = true;

    public function init()
    {
        $vars = $this->getPersist(static::PERSIST_DB);
        if (empty($vars)) {
            $vars = array(
                    'DB_HOST'       => 'localhost',
                    'DB_USER'       => '',
                    'DB_PASS'       => '',
                    'DB_DBNAME'     => 'pi',
                    'DB_PREFIX'     => 'p' . substr(md5(time()), 0, 3),
            );
            $this->setPersist(static::PERSIST_DB, $vars);
        }

        $this->vars = $vars;
    }

    protected function normalizeParameters(array $vars)
    {
        //$dsn = 'mysql:dbname=' . $vars['DB_DBNAME'] . ';';
        $dsn = 'mysql:';
        if (strpos($vars['DB_HOST'], '/')) {
            $dsn .= 'unix_socket=' . $vars['DB_HOST'];
        } elseif (strpos($vars['DB_HOST'], ':')) {
            list($host, $port) = explode(':', $vars['DB_HOST'], 2);
            $dsn .= 'host=' . $host . ';port=' . $port;
        } else {
            $dsn .= 'host=' . $vars['DB_HOST'];
        }
        $params = array(
            'driver'        => 'pdo',
            'dsn'           => $dsn,
            'username'      => $vars['DB_USER'],
            'password'      => $vars['DB_PASS'],
            'schema'        => $vars['DB_DBNAME'],
            'table_prefix'  => $vars['DB_PREFIX'] . '_'
        );

        return $params;
    }

    protected function connection()
    {
        $dbConfig = $this->wizard->getConfig('database');
        $vars = $this->normalizeParameters($this->vars);
        $options = array(
            PDO::MYSQL_ATTR_INIT_COMMAND    => sprintf(
                'SET NAMES %s COLLATE %s',
                $dbConfig['charset'],
                $dbConfig['collate']
            ),
            PDO::ATTR_PERSISTENT            => false,
        );
        $this->dbLink = new PDO(
            $vars['dsn'],
            $vars['username'],
            $vars['password'],
            $options
        );
    }

    public function connectAction()
    {
        try {
            $this->connection();
            $result = '1';
        } catch (\Exception $e) {
            $result = '0';
        }

        echo $result;
    }

    public function setAction()
    {
        $var = $this->request->getParam('var');
        $val = $this->request->getParam('val', '');
        $this->vars[$var] = $val;
        $this->setPersist(static::PERSIST_DB, $this->vars);

        echo '1';
    }

    public function submitAction()
    {
        $vars =& $this->vars;
        foreach (array_keys($vars) as $name) {
            $vars[$name] = $this->request->getPost($name);
        }
        $this->setPersist(static::PERSIST_DB, $vars);
        $params = $this->normalizeParameters($vars);
        $dbConfig = $this->wizard->getConfig('database');
        $params = array_merge($params, $dbConfig);

        $error = '';
        try {
            $this->connection();
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        if (!$error) {
            try {
                // Create database if not exist
                $sql = sprintf(
                    'CREATE DATABASE IF NOT EXISTS `%s`',
                    $params['schema']
                );
                $result = $this->dbLink->exec($sql);
                if (!$result) {
                    $errorInfo = $this->dbLink->errorInfo();
                    $error = $errorInfo[1] . ':' . $errorInfo[2];
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }
        if (!$error) {
            try {
                $sql = sprintf(
                    'ALTER DATABASE `%s` DEFAULT CHARACTER SET %s COLLATE %s',
                    $params['schema'],
                    $params['charset'],
                    $params['collate']
                );
                $result = $this->dbLink->exec($sql);
                if (!$result) {
                    $errorInfo = $this->dbLink->errorInfo();
                    $error = $errorInfo[1] . ':' . $errorInfo[2];
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }
        if ($error) {
            $this->status = -1;
            $content = '<div class="alert alert-danger">'
                . '<h1>' . _s('Database validation is failed.') . '</h1>'
                . '<p>' . $error . '</p>'
                . '</div>';
        } else {
            $file = Pi::path('config') . '/service.database.php';
            $file_dist = $this->wizard->getRoot()
                . '/dist/service.database.php.dist';
            $content = file_get_contents($file_dist);
            foreach ($params as $var => $val) {
                $content = str_replace('%' . $var . '%', $val, $content);
            }

            $error_dsn = false;
            if (!$file = fopen($file, 'w')) {
                $error_dsn = true;
            } else {
                $result = fwrite($file, $content);
                if ($result == false || $result < 1) {
                    $error_dsn = true;
                }
                fclose($file);
            }
            if (empty($error_dsn)) {
                $this->status = 1;
            } else {
                $errorDsn = array('file' => $file, 'content' => $content);
            }

            if (!empty($errorDsn)) {
                $content .= '<h3>' . _s('Configuration file write error') . '</h3>'
                    . '<p class="caption" style="margin-top: 10px;">'
                    . sprintf(
                        _s('The configuration file "%s" is not written correctly.'),
                        $errorDsn['file']
                    )
                    . '</p><textarea cols="80" rows="10" class="span12">'
                    . $errorDsn['content']
                    . '</textarea>';
            }
        }
        $this->content .= $content;
    }

    public function indexAction()
    {
        $this->loadForm();
    }

    protected function loadForm()
    {
        $this->hasForm = true;
        $vars = $this->vars;

        $elementInfo = array(
            'DB_HOST'   => array(
                _s('Server hostname'),
                _s('Hostname (and port, delimited by ":") or Unix socket of the database server. If you are unsure, "localhost" works in most cases, or "127.0.0.1"'),
            ),
            'DB_USER'   => array(
                _s('User name'),
                _s('Name of the user account that will be used to connect to the database server'),
            ),
            'DB_PASS'   => array(
                _s('Password'),
                _s('Password of your database user account'),
            ),
            'DB_DBNAME'   => array(
                _s('Database name'),
                _s('The name of database on the host. The database must be already available.'),
            ),
            'DB_PREFIX'     => array(
                _s('Table prefix'),
                _s('This prefix will be added to all new tables created to avoid name conflicts in the database. If you are unsure, just keep the default.'),
            ),
        );

        $displayInput = function ($item) use ($vars, $elementInfo) {
            $content = '<div class="item">'
                     . '<label for="' . $item . '" class="">'
                     . $elementInfo[$item][0] . '</label>'
                     . '<p class="caption">' . $elementInfo[$item][1] . '</p>'
                     . '<input type="text" name="' . $item . '" id="'
                     . $item . '" value="' . $vars[$item] . '" />'
                     . '<em id="' . $item . '-status" class="">&nbsp;</em>'
                     . '</div>';

            return $content;
        };

        $content = '';
        $content .= $displayInput('DB_HOST');
        $content .= $displayInput('DB_USER');

        $item = 'DB_PASS';
        $content .= '<div class="item">'
                  . '<label for="' . $item . '">'
                  . $elementInfo[$item][0] . '</label>'
                  . '<p class="caption">' . $elementInfo[$item][1] . '</p>'
                  . '<input type="password" name="' . $item . '" id="'
                  . $item . '" value="" />'
                  . '</div>';

        $content .= $displayInput('DB_DBNAME');
        $content .= $displayInput('DB_PREFIX');

        $contentSetup = '<div class="well">'
                      . '<h2><span id="db-connection-label" class="">'
                      . _s('Database setup') . '</span></h2>'
                      . '<p class="caption">' . _s('Settings for database')
                      . '</p>'
                      . $content
                      . '</div>';

        $this->content = $contentSetup;

        $this->headContent .=<<<'STYLE'
<style type="text/css" media="screen">
    .item {
        margin-top: 20px;
    }
</style>
STYLE;

        $this->footContent .=<<<SCRIPT
<script>
var url="$_SERVER[PHP_SELF]";
$(document).ready(function(){
    $("input[type=text], input[type=password]").each(function(index) {
        update($(this).attr("name"));
    });
    $("#DB_HOST, #DB_USER, #DB_DBNAME, #DB_PREFIX").each(function(index) {
        checkEmpty($(this).attr("id"));
    });

    checkConnection();
    $("#DB_HOST, #DB_USER, #DB_PASS, #DB_DBNAME").each(function(index) {
        $(this).bind("change", function() {
            updateConnection($(this).attr("name"), this.value);
        });
    });
    $("#DB_PREFIX").each(function(index) {
        $(this).bind("change", function() {
            update($(this).attr("name"));
        });
    });
});

function checkEmpty(id) {
    var val = $.trim($("#" + id).val());
    if (val.length == 0) {
        $("#" + id + "-status").attr("class", "failure");
    } else {
        $("#" + id + "-status").attr("class", "");
    }
}

function updateConnection(v, val) {
    $("#db-connection-label").attr("class", "loading");
    $.get(url,
        {"action": "set", "var": v, "val": val, "page": "database"},
        function (data) {
        if (data) {
            checkConnection();
            checkEmpty(v);
        }
    });
}

function update(id) {
    $.get(url,
        {"action": "set", "var": id, "val": $("#" + id).val(),
            "page": "database"},
        function (data) {
        if (data) {
            checkEmpty(id);
        }
    });
}

function checkConnection() {
    if ($("#DB_HOST").val() && $("#DB_USER").val()) {
        $.get(url, {"action": "connect", "page": "database"}, function (data) {
            var statusClass = "failure";
            if (data == 1) {
                statusClass = "success";
            }
            $("#db-connection-label").attr("class", statusClass);
        });
    }
}
</script>
SCRIPT;
    }
}
