<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Log\Writer;

use Pi;
use Pi\Log\Logger;
use Pi\Log\Formatter\Debugger as DebuggerFormatter;
use Pi\Log\Formatter\DbProfiler as DbFormatter;
use Pi\Log\Formatter\Profiler as ProfilerFormatter;
use Pi\Log\Formatter\SystemInfo as SystemInfoFormatter;
use Pi\Version\Version as PiVersion;
use Zend\Log\Formatter\FormatterInterface;
use Zend\Log\Writer\AbstractWriter;
use PDO;

/**
 * Debugger writer
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Debugger extends AbstractWriter
{
    /**
     * {@inheritDoc}
     */
    protected $errorsToExceptionsConversionLevel = E_ALL;

    /** @var ProfilerFormatter Profile formatter */
    protected $profilerFormatter;

    /** @var DbFormatter DbProfile formatter */
    protected $dbProfilerFormatter;

    /** @var SystemInfoFormatter SystemInfo formatter */
    protected $systemInfoFormatter;

    /**
     * Log messages container
     *
     * @var array
     */
    protected $logger = array(
        'log'       => array(),
        'debug'     => array(),
        'profiler'  => array(),
        'db'        => array(),
        'system'    => array(),
    );

    /**
     * The Debugger is muted
     *
     * @var bool
     */
    protected $muted = false;

    /**
     * Mute the debugger
     *
     * @param bool $flag
     * @return bool Return previous muted value
     */
    public function mute($flag = true)
    {
        $muted = $this->muted;
        if (null !== $flag) {
            $this->muted = (bool) $flag;
        }

        return $muted;
    }

    /**
     * Get formatter for loggder writer
     *
     * @return DebuggerFormatter
     */
    public function formatter()
    {
        if (!$this->formatter) {
            $this->formatter = new DebuggerFormatter;
        }

        return $this->formatter;
    }

    /**
     * Get formatter for profiler writer
     *
     * @return ProfilerFormatter
     */
    public function profilerFormatter()
    {
        if (!$this->profilerFormatter) {
            $this->profilerFormatter = new ProfilerFormatter;
        }

        return $this->profilerFormatter;
    }

    /**
     * Get formatter for DB profiler writer
     *
     * @return DbFormatter
     */
    public function dbProfilerFormatter()
    {
        if (!$this->dbProfilerFormatter) {
            $this->dbProfilerFormatter = new DbFormatter;
        }

        return $this->dbProfilerFormatter;
    }

    /**
     * Get formatter for system info writer
     *
     * @return SystemInfoFormatter
     */
    public function systemInfoFormatter()
    {
        if (!$this->systemInfoFormatter) {
            $this->systemInfoFormatter = new SystemInfoFormatter;
        }

        return $this->systemInfoFormatter;
    }

    /**
     * Register a message to logger.
     *
     * @param array $event event data
     * @return void
     */
    protected function doWrite(array $event)
    {
        if ($this->muted) {
            return;
        }
        if ($event['priority'] > Logger::DEBUG) {
            return;
        }
        $message = $event;
        if ($this->formatter() instanceof FormatterInterface) {
            $message = $this->formatter()->format($event);
        }

        if ($event['priority'] == Logger::DEBUG) {
            $this->logger['debug'][] = $message;
        } else {
            $this->logger['log'][] = $message;
        }
    }

    /**
     * Register a message to profiler
     *
     * @param array $event event data
     * @return void
     */
    public function doProfiler(array $event)
    {
        if ($this->muted) {
            return;
        }
        $message = $this->profilerFormatter()->format($event);

        $this->logger['profiler'][] = $message;
    }

    /**
     * Register a message to DB profiler
     *
     * @param array $event event data
     * @return void
     */
    public function doDb(array $event)
    {
        if ($this->muted) {
            return;
        }
        $message = $this->dbProfilerFormatter()->format($event);
        $this->logger['db'][] = $message;
    }

    /**
     * Process system information and register to systeminfo writer
     *
     * @return void
     */
    public function systemInfo()
    {
        $system = array();

        // Execution time
        $system['Execution time'] = sprintf(
            '%.4f',
            microtime(true) - Pi::startTime()
        ) . ' s';

        // Included file count
        $files_included = get_included_files();
        $system['Included files'] = count ($files_included) . ' files';

        // Memory usage
        $memory = 0;
        if (function_exists('memory_get_usage')) {
            $memory = memory_get_usage();
            if (function_exists('memory_get_peak_usage')) {
                $memory .= '; peak: ' . memory_get_peak_usage();
            }
            $memory .= ' bytes';
        } else {
            // Windows system
            if (strpos(strtolower(PHP_OS), 'win') !== false) {
                $out = array();
                exec(
                    'tasklist /FI "PID eq ' . getmypid() . '" /FO LIST',
                    $out
                );
                $memory = substr($out[5], strpos($out[5], ':') + 1);
            }
        }
        $system['Memory usage'] = $memory ?: 'Not detected';

        // Sstem environments
        $system['OS'] = PHP_OS ?: 'Not detected';
        // PHP_SAPI ?: 'Not detected';
        $system['Web Server'] = $_SERVER['SERVER_SOFTWARE'];
        $system['PHP Version'] = PHP_VERSION;

        // MySQL version
        $pdo = Pi::db()->getAdapter()->getDriver()
            ->getConnection()->connect()->getResource();
        $server_version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        $client_version = $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION);
        $system['MySQL Version'] = sprintf('Server: %s; Client: %s',
            $server_version, $client_version);

        // Application versions
        $system['Pi Version'] = PiVersion::version();
        $system['Zend Version'] = PiVersion::version('zend');
        $system['Persist Engine'] = Pi::persist()->getType();
        if (Pi::service()->hasService('cache')) {
            $class = get_class(Pi::service('cache')->storage());
            $system['Cache Storage'] = $class;
        }
        if (Pi::service()->hasService('module')) {
            $system['Module'] = Pi::service('module')->current() ?: 'N/A';
        }
        if (Pi::service()->hasService('theme')) {
            $system['Theme'] = Pi::service('theme')->current();
        }

        // Affecting PHP's Behaviour
        // See: http://www.php.net/manual/en/refs.basic.php.php
        $extensions = array();
        // APC
        $APCEnabled = ini_get('apc.enabled');
        if (PHP_SAPI == 'cli') {
            $APCEnabled = $APCEnabled && (bool) ini_get('apc.enable_cli');
        }
        if ($APCEnabled) {
            $extensions[] = 'APC: ' . phpversion('apc');
        }
        // APD
        if (function_exists('apd_set_pprof_trace')) {
            $extensions[] = 'APD: ' . APD_VERSION;
        }
        // XHProf
        if (function_exists('xhprof_enable')) {
            $extensions[] = 'XHProf';
        }
        // Xdebug
        if (extension_loaded('xdebug')) {
            $extensions[] = 'Xdebug';
        }
        // Intl
        if (extension_loaded('intl')) {
            $extensions[] = 'Intl';
        }
        // cURL
        if (function_exists('curl_exec')) {
            $extensions[] = 'cURL';
        }

        if ($extensions) {
            $system['Extensions'] = implode('; ', $extensions);
        }

        foreach ($system as $key => $value) {
            $event = array(
                'name'  => $key,
                'value' => $value,
            );
            $this->logger['system'][] =
                $this->systemInfoFormatter()->format($event);
        }
    }

    /**
     * Render and display logged messages
     *
     * @return void
     */
    public function render()
    {
        if ($this->muted) {
            return;
        }
        $this->systemInfo();

        // Use heredoc for log contents
        $log =
<<<'EOT'
<div id="pi-logger-output">
    <div id="pi-logger-tabs">
EOT;
        foreach (array_keys($this->logger) as $category) {
            $count = count($this->logger[$category]);
            $log .= PHP_EOL .
<<<"EOT"
        <span id="pi-logger-tab-{$category}">
            <a href="javascript:piLoggerToggleCategoryDisplay('{$category}')">
                {$category}({$count})
            </a>
        </span> |
EOT;
        }

        $log .= PHP_EOL .
<<<'EOT'
        <span id="pi-logger-tab-all">
            <a href="javascript:piLoggerToggleCategoryDisplay('all')">all</a>
        </span>
    </div>
    <div id="pi-logger-categories">
EOT;

        foreach ($this->logger as $category => $events) {
            $eventString = implode(PHP_EOL, $events);
            $log .= PHP_EOL .
<<<"EOT"
        <div id="pi-logger-category-{$category}" class="pi-events">
            <div class="pi-category">{$category}</div>
            <!-- Event list starts -->
            {$eventString}
            <!-- Event list ends -->
        </div>
EOT;
        }
        $log .= PHP_EOL .
<<<'EOT'
    </div>
</div>
EOT;

        // Use nowdoc for CSS contents
        $scripts_css =
<<<'EOT'
<style type="text/css">
    #pi-logger-output {
        font-family: monospace;
        font-size: 90%;
        padding: 10px;
    }

    #pi-logger-output #pi-logger-tabs {
        border-top: 1px solid;
    }

    #pi-logger-output #pi-logger-categories {
        display: none;
    }

    #pi-logger-output a,
    #pi-logger-output a:visited {
        font-weight: normal;
        color: inherit;
    }

    #pi-logger-output div.pi-events {
        clear: both;
    }

    #pi-logger-output div.pi-category {
        font-weight: bold;
        padding: 10px 0 5px 0;
    }

    #pi-logger-output div.pi-event {
        clear: both;
    }

    #pi-logger-output div.pi-event .time {
        font-weight: bold;
    }

    #pi-logger-output div.pi-event .message {
        margin-left: 50px;
        font-weight: normal;
    }

    #pi-logger-output #pi-logger-errors .pi-event .message {
        color: red;
    }

    #pi-logger-output .pi-event .error,
    #pi-logger-output .pi-event .err {
        color: #FF0000;
        font-weight: bold;
    }

    #pi-logger-output .pi-event .exception {
        color: #FF0000;
    }

    #pi-logger-output .pi-event .warning,
    #pi-logger-output .pi-event .warn {
        color: #D2691E;
    }

    #pi-logger-output .pi-event .notice {
        color: #A0522D;
    }

    #pi-logger-output .pi-event .message span {
        padding-left: 5px;
    }

    #pi-logger-output .pi-event .message .label {
        width: 150px;
        text-align: right;
        float: left;
        font-weight: bold;
        padding: 2px 5px;
    }

    #pi-logger-output .pi-event .message .text {
        display: block;
        float: left;
        padding: 2px 5px;
    }
</style>
EOT;

        $cookiePath = ($baseUrl = Pi::host()->get('baseUrl'))
                        ? rtrim($baseUrl, '/') . '/' : '/';
        // Use heredoc for JavaScript contents
        $scripts_js =
<<<"EOT"
<script type="text/javascript">
    var cookiePath = "{$cookiePath}";
    var cookieName = "pi-logger";
    function piLoggerCreateCookie(name,value) {
        value = value ? "+" : "-";
        document.cookie = cookieName+"=["+name+value+"]; path=" + cookiePath;
    }
    function piLoggerReadCookie() {
        var ret = new Array("", 0);
        var nameEQ = cookieName + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) {
                var valid = c.substring(c.indexOf("[")+1, c.indexOf("]"));
                ret[0] = valid.substring(0,valid.length-1);
                var str = valid.substring(valid.length-1,valid.length);
                ret[1] = (str == "+") ? 1 : 0;
                return ret;
            }
        }
        return ret;
    }

    // Toggle display for a category and set corresponding cookies
    function piLoggerToggleCategoryDisplay(name) {
        var data = piLoggerReadCookie();
        var loggerview  = (name == data[0]) ? (data[1] ? 0 : 1) : 1;
        return piLoggerSetCategoryDisplay(name, loggerview);
    }

    // Set display for a specified category
    function piLoggerSetCategoryDisplay(name, loggerview) {
        var logElement = document.getElementById("pi-logger-categories");
        if (!logElement) return;
        var old = piLoggerReadCookie();
        var oldElt = document.getElementById("pi-logger-tab-" + old[0]);
        if (oldElt) {
            oldElt.style.textDecoration = "none";
        }
        var i, elt;
        for (i=0; i!=logElement.childNodes.length; i++) {
            elt = logElement.childNodes[i];
            if (!elt.tagName || elt.tagName.toLowerCase() != 'div' || !elt.id) continue;
            var elestyle = elt.style;
            if (name == 'all' || elt.id == "pi-logger-category-" + name) {
                if (loggerview) {
                    elestyle.display = "block";
                    document.getElementById("pi-logger-tab-" + name).style.textDecoration = "underline";
                } else {
                    elestyle.display = "none";
                    document.getElementById("pi-logger-tab-" + name).style.textDecoration = "none";
                }
            } else {
                elestyle.display = "none";
            }
        }
        logElement.style.display = "block";
        piLoggerCreateCookie(name, loggerview);
    }

    // Not used
    function piLoggerToggleElementDisplay(id) {
        var elestyle = document.getElementById(id).style;
        if (elestyle.display == "none") {
            elestyle.display = "block";
        } else {
            elestyle.display = "none";
        }
    }

    // Set logger view for categories
    function piLoggerSetView(data) {
        return piLoggerSetCategoryDisplay(data[0], data[1]);
    }

    // Set logger output view
    var data = piLoggerReadCookie();
    piLoggerSetView(data);
</script>
EOT;

        echo PHP_EOL . $scripts_css . PHP_EOL. $log . PHP_EOL . $scripts_js;
    }
}
