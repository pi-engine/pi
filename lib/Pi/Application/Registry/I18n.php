<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
     */
    protected function loadDynamic($options)
    {
        $translator = Pi::service('i18n')->getTranslator();
        if (!isset($options['custom']) || !empty($options['custom'])) {
            $optionsCustom = $options;
            $optionsCustom['domain'] = 'custom/' . $options['domain'];
            //d($optionsCustom);
            $custom = $translator->loadResource($optionsCustom);
            //d($custom);
        } else {
            $custom = array();
        }
        if (empty($options['custom'])) {
            //d($options);
            $result = $translator->loadResource($options);
            //d($result);
            $result = array_merge($result, $custom);
        } else {
            $result = $custom;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     * @param string|string[]|array $rawDomain
     * @param string                $locale
     * @param bool|null             $custom
     */
    public function read($rawDomain = '', $locale = '', $custom = null)
    {
        //$this->cache = false;
        // Canonize locale
        $locale = $locale ?: Pi::service('i18n')->getLocale();

        // Canonize domain
        if (is_array($rawDomain)) {
            if (!array_key_exists(0, $rawDomain)) {
                extract($rawDomain);
            } else {
                list($domain, $file) = $rawDomain;
            }
        } else {
            list($domain, $file) =
                Pi::service('i18n')->canonizeDomain($rawDomain);
            if ('custom/' == substr($domain, 0, 7)) {
                $custom = true;
                $domain = substr($domain, 7);
            }
        }
        $moduleDomain = Pi::service('i18n')->moduleDomain;
        if ($moduleDomain == substr($domain, 0, strlen($moduleDomain))) {
            $namespace = substr($domain, strlen($moduleDomain) + 1) ?: '';
        } else {
            //d($domain);
            $namespace = static::NAMESPACE_GLOBAL;
        }

        $this->namespaceCustom = $namespace;
        if (null === $custom) {
            $options = compact('domain', 'file', 'locale');
        } else {
            $custom = (int) $custom;
            $options = compact('domain', 'file', 'locale', 'custom');
        }

        //d($options);
        $data = $this->loadData($options);

        //d($data);
        return $data;
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
