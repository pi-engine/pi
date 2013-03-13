<?php
/**
 * Pi Engine translator
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
 * @package         Pi\I18n
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\I18n\Translator;

use Pi;
use Zend\I18n\Translator\Translator as ZendTranslator;
use Zend\I18n\Translator\Loader\FileLoaderInterface;
use Zend\I18n\Translator\TextDomain;

class Translator extends ZendTranslator
{
    const DEFAULT_LOCALE = 'en';

    /**
     * Previous locale.
     *
     * @var string
     */
    protected $previousLocale;

    /**
     * Text domain
     *
     * @var string
     */
    protected $textDomain = 'default';

    /**
     * Previous text domain
     *
     * @var string
     */
    protected $previousTextDomain = 'default';

    /**
     * Resource loader
     *
     * @var FileLoaderInterface
     */
    protected $loader;

    /**
     * Set the default locale.
     *
     * @param  string $locale
     * @return Translator
     */
    public function setLocale($locale)
    {
        if (null !== $locale && $locale != $this->locale) {
            $this->previousLocale = $this->locale;
            $this->locale = $locale;
        }
        return $this;
    }

    /**
     * Get the default locale.
     *
     * @return string
     */
    public function getLocale()
    {
        if ($this->locale === null) {
            $this->locale = static::DEFAULT_LOCALE;
        }

        return $this->locale;
    }

    /**
     * Get the fallback locale.
     *
     * @return string
     */
    public function getFallbackLocale()
    {
        if ($this->fallbackLocale === null) {
            $this->fallbackLocale = static::DEFAULT_LOCALE;
        }

        return $this->fallbackLocale;
    }

    /**
     * Restore the default locale.
     *
     * @return Translator
     */
    public function restoreLocale()
    {
        $this->locale = $this->previousLocale ?: $this->locale;
        return $this;
    }

    /**
     * Set the text doamin
     *
     * @param  string $textDoamin
     * @return Translator
     */
    public function setTextDomain($textDomain)
    {
        if ($textDomain != $this->textDomain) {
            $this->previoustextDomain = $this->textDomain;
            $this->textDomain = $textDomain;
        }
        return $this;
    }

    /**
     * Get the text domain
     *
     * @return string
     */
    public function getTextDomain()
    {
        return $this->textDomain;
    }

    /**
     * Restore the text domain
     *
     * @return Translator
     */
    public function restoreTextDomain()
    {
        $this->textDomain = $this->previoustextDomain;
        return $this;
    }

    /**
     * Restore text domain and locale
     *
     * @return Translator
     */
    public function restore()
    {
        $this->locale = $this->previousLocale ?: $this->locale;
        $this->textDomain = $this->previoustextDomain;
        return $this;
    }

    /**
     * Set resource loader
     *
     * @param LoaderInterface $loader
     * @return Translator
     */
    public function setLoader($loader)
    {
        $this->loader = $loader;
        return $this;
    }

    /**
     * Get resource loader
     *
     * @return LoaderInterface
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Translate a message.
     *
     * @param  string $message
     * @param  string|null $textDomain
     * @param  string|null $locale
     * @return string
     */
    public function translate($message, $textDomain = null, $locale = null)
    {
        if (null === $textDomain) {
            $textDomain = $this->getTextDomain();
        }
        //d($textDomain);
        return parent::translate($message, $textDomain, $locale);
    }

    /**
     * Translate a plural message.
     *
     * @param  string      $singular
     * @param  string      $plural
     * @param  int         $number
     * @param  string|null $textDomain
     * @param  string|null $locale
     * @return string
     */
    public function translatePlural(
        $singular,
        $plural,
        $number,
        $textDomain = null,
        $locale = null
    ) {
        if (!$textDomain) {
            $textDomain = $this->getTextDomain();
        }
        return parent::translatePlural($singular, $plural, $number, $textDomain, $locale);
    }

    /**
     * Get a translated message.
     *
     * @param  string      $message
     * @param  string      $locale
     * @param  string      $textDomain
     * @param  boolean     $returnPluralRule
     * @return string|null
     */
    protected function getTranslatedMessage(
        $message,
        $locale = null,
        $textDomain = null,
        $returnPluralRule = false
    ) {
        if ($message === '') {
            return '';
        }

        if (!$textDomain) {
            $textDomain = $this->getTextDomain();
        }

        if (isset($this->messages[$textDomain][$locale][$message])) {
            return $this->messages[$textDomain][$locale][$message];
        }

        if (isset($this->messages[''][$locale][$message])) {
            return $this->messages[''][$locale][$message];
        }

        //return parent::getTranslatedMessage($message, $locale, $textDomain, $returnPluralRule);

        return null;
    }

    /**
     * Load translation resource, existent data will be flushed
     *
     * @param array|string $domain
     * @param string|null $locale
     * @return Translator
     */
    public function load($domain, $locale = null)
    {
        // Array of ($textDomain, $file)
        $domain = is_array($domain) ? $domain : Pi::service('i18n')->normalizeDomain($domain);
        $this->setTextDomain($domain[0]);
        $this->setLocale($locale);

        $messages = Pi::service('registry')->i18n->setGenerator(array($this, 'loadResource'))->read($domain, $this->locale);
        $this->messages[$this->textDomain][$this->locale] = new TextDomain($messages);
        //$this->messages[$this->textDomain][$this->locale] = $messages;
        if ($this->textDomain && $messages) {
            if (!empty($this->messages[''][$this->locale])) {
                foreach ($messages as $key => $val) {
                    $this->messages[''][$this->locale]->offsetSet($key, $val);
                }
                //$this->messages[''][$this->locale]->append($messages);
                //$this->messages[''][$this->locale]->append($messages->getArrayCopy());
            } else {
                $this->messages[''][$this->locale] = new TextDomain($messages);
                //$this->messages[''][$this->locale] = $messages;
            }
        }
        return $this;
    }

    /**
     * Load translation resource
     *
     * @param array $options
     * @return TextDomain
     */
    public function loadResource($options)
    {
        $filename = Pi::service('i18n')->getPath(array($options['domain'], $options['file']), $options['locale']);

        return $this->loader->load($options['locale'], $filename);
    }
}
