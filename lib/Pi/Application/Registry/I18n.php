<?php
/**
 * Pi cache registry
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
 * @package         Pi\Application
 * @subpackage      Registry
 * @version         $Id$
 */

namespace Pi\Application\Registry;
use Pi;

class I18n extends AbstractRegistry
{
    const NAMESPACE_GLOBAL = '_usr';
    protected $namespaceCustom = '';

    public function read($rawDomain, $locale)
    {
        if (is_array($rawDomain)) {
            if (!array_key_exists(0, $rawDomain)) {
                extract($rawDomain);
            } else {
                list($domain, $file) = $rawDomain;
            }
        } else {
            list($domain, $file) = Pi::service('i18n')->normalizeDomain($rawDomain);
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

    public function create($domain)
    {
        $this->flush();
        $this->read($domain);
        return true;
    }

    public function setNamespace($meta)
    {
        return parent::setNamespace($this->namespaceCustom);
    }

    public function flush()
    {
        $this->clear(static::NAMESPACE_GLOBAL);
        $this->flushByModules();
        return $this;
    }
}
