<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Registry
 */

namespace Pi\Application\Registry;

use Pi;

/**
 * I18n language file list
 *
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @see Pi\I18n\Translator\Translator::loadResource() for generator callback
 */
class I18n extends AbstractRegistry
{
    /**
     * Namespace for global
     *
     * @var string
     */
    const NAMESPACE_GLOBAL = '_usr';

    /**
     * Custom namespace
     *
     * @var string
     */
    protected $namespaceCustom = '';

    /**
     * {@inheritDoc}
     * @param string|array  $rawDomain
     * @param string        $locale
     */
    public function read($rawDomain = '', $locale = '')
    {
        $locale = $locale ?: Pi::service('i18n')->getLocale();

        if (is_array($rawDomain)) {
            if (!array_key_exists(0, $rawDomain)) {
                extract($rawDomain);
            } else {
                list($domain, $file) = $rawDomain;
            }
        } else {
            list($domain, $file) =
                Pi::service('i18n')->normalizeDomain($rawDomain);
        }
        $moduleDomain = Pi::service('i18n')->moduleDomain;
        if ($moduleDomain == substr($domain, 0, strlen($moduleDomain))) {
            $namespace = substr($domain, strlen($moduleDomain) + 1) ?: '';
        } else {
            $namespace = static::NAMESPACE_GLOBAL;
        }

        $this->namespaceCustom = $namespace;
        $options = compact('domain', 'file', 'locale');

        return $this->loadData($options);
    }

    /**
     * {@inheritDoc}
     * @param string    $domain
     */
    public function create($domain = '')
    {
        $this->flush();
        $this->read($domain);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function setNamespace($meta = '')
    {
        return parent::setNamespace($this->namespaceCustom);
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        $this->clear(static::NAMESPACE_GLOBAL);
        $this->flushByModules();

        return $this;
    }
}
