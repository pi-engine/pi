<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Exception;
use Memcached as MemcachedExtension;

/**
 * Memcached service
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Memcached extends AbstractService
{
    /** @var array Instances */
    protected static $instances = array();

    /** @var int Default port */
    const DEFAULT_PORT =  11211;

    /** @var int Default weight */
    const DEFAULT_WEIGHT  = 1;

    /**
     * Load options
     *
     * @param string|array $config
     * @return array
     * @see http://www.php.net/manual/en/memcached.constants.php
     *      for Memcached predefined constants
     */
    protected function loadOptions($config)
    {
        if (is_string($config)) {
            $config = Pi::config()->load(sprintf('memcached.%s.php', $config));
        }

        $options = array();
        if (!empty($config['client'])) {
            $clients = array();
            // setup memcached client options
            foreach ($config['client'] as $name => $value) {
                $optId = null;
                if (is_int($name)) {
                    $optId = $name;
                } else {
                    $optConst = 'Memcached::OPT_' . strtoupper($name);
                    if (defined($optConst)) {
                        $optId = constant($optConst);
                    } else {
                        $msg = 'Unknown memcached client option "%s" (%s)';
                        trigger_error(srpintf($msg, $name, $optConst));
                    }
                }
                if ($optId) {
                    if (is_string($value)) {
                        $memcachedValue = 'Memcached::' . strtoupper($value);
                        $value = defined($memcachedValue)
                            ? constant($memcachedValue) : $value;
                    }
                    $clients[$optId] = $value;
                }
            }
            if (!empty($clients)) {
                $options['client'] = $clients;
            }
            unset($config['client']);
        }

        // setup memcached servers
        $serverList = isset($config['servers']) ? $config['servers'] : $config;
        if (isset($serverList['host'])) {
            // Transform it into associative arrays
            $serverList = array(0 => $serverList);
        }
        $servers = array();
        foreach ($serverList as $idx => $server) {
            if (!array_key_exists('port', $server)) {
                $server['port'] = static::DEFAULT_PORT;
            }
            if (!array_key_exists('weight', $server)) {
                $server['weight'] = static::DEFAULT_WEIGHT;
            }
            $servers[] = array(
                $server['host'],
                $server['port'],
                $server['weight']
            );
        }
        if (!empty($servers)) {
            $options['servers'] = $servers;
        } else {
            $options = array();
        }

        return $options;
    }

    /**
     * Load a memcached instance
     *
     * @param array|null $config
     * @return MemcachedExtension
     * @throws exception
     */
    public function load($config = null)
    {
        if (!extension_loaded('memcached')) {
            throw new exception('Memcached extension is not available!');
        }
        // Load default Memcached handler from Pi::persist to keep consistency
        if (empty($config)) {
            return Pi::persist()->loadHandler('Memcached')->getEngine();
        }

        $configKey = is_array($config) ? serialize($config) : $config;
        if (isset(static::$instances[$configKey])) {
            return static::$instances[$configKey];
        }

        static::$instances[$configKey] = false;
        $options = $this->loadOptions($config);
        if (empty($options)) {
            throw new exception('No valid options!');
        }
        $memcached = new MemcachedExtension;
        if (!empty($options['client'])) {
            // setup memcached client options
            foreach ($options['client'] as $optId => $value) {
                if (!$memcached->setOption($optId, $value)) {
                    $msg = 'Setting memcached client option "%s" failed';
                    trigger_error(sprintf($msg, $optId));
                }
            }
        }
        $memcached->addServers($options['servers']);
        static::$instances[$configKey] = $memcached;

        return static::$instances[$configKey];
    }
}
