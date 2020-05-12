<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Editor\Ckeditor\View\Helper;

use Pi;
use Pi\Form\View\Helper\AbstractEditor;
use Laminas\Form\ElementInterface;

/**
 * Editor element helper
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FormEditorCkeditor extends AbstractEditor
{
    /** @var string */
    protected $configFile = 'editor.ckeditor.php';

    /**
     * A boolean variable indicating whether CKEditor has been initialized.
     * Set it to true only if you have already included
     * &lt;script&gt; tag loading ckeditor.js in your website.
     *
     * @var bool
     */
    protected $initialized = false;

    /**
     * An array that holds global event listeners.
     *
     * @var array
     */
    protected $renderedGlobalEvents = [];

    /**
     * {@inheritDoc}
     */
    public function render(ElementInterface $element)
    {
        $configs      = [];
        $events       = [];
        $globalEvents = [];

        $options = $element->getOptions();
        if (!empty($this->options['config'])) {
            $configs = (array)$this->options['config'];
        }
        if (!empty($options['config'])) {
            $configs = array_merge($configs, $options['config']);
        }
        if (!empty($this->options['events'])) {
            $events = (array)$this->options['events'];
        }
        if (!empty($options['events'])) {
            $events = array_merge($events, $options['events']);
        }
        if (!empty($this->options['global_events'])) {
            $globalEvents = (array)$this->options['global_events'];
        }
        if (!empty($options['global_events'])) {
            $globalEvents = array_merge($globalEvents, $options['global_events']);
        }
        if (!empty($this->options['attributes'])) {
            $element->setAttributes((array)$this->options['attributes']);
        }
        $this->init();
        $id = $element->getAttribute('id');
        if (!$id) {
            $id = uniqid($element->getName() . '_');
            $element->setAttribute('id', $id);
        }
        $html = parent::render($element);

        $js = $this->renderGlobalEvents($globalEvents);
        //$name = $element->getAttribute('id') ?: $element->getName();
        $config = $this->configSettings($configs, $events);
        if (!empty($config)) {
            $js .= 'CKEDITOR.replace("' . $id . '", ' . json_encode($config) . ');';
        } else {
            $js .= 'CKEDITOR.replace("' . $id . '");';
        }
        $this->view->footScript()->appendScript($js);

        return $html;
    }

    /**
     * Initializes CKEditor (executed only once)
     *
     * @return string
     */
    protected function init()
    {
        if (!$this->initialized) {
            $section  = Pi::engine()->section();
            $basePath = Pi::url('script') . '/editor/ckeditor';
            // Set load config file
            switch ($section) {
                case 'front':
                    $config = sprintf("CKEDITOR.config.customConfig = '%s/config-front.js';", $basePath);
                    break;

                case 'admin':
                    $config = sprintf("CKEDITOR.config.customConfig = '%s/config-admin.js';", $basePath);
                    break;
            }
            // Set view
            $this->view->footScript()->appendScript('window.CKEDITOR_BASEPATH="' . $basePath . '/";');
            $this->view->footScript()->appendScript($config);
            $this->view->headScript()->appendFile($basePath . '/ckeditor.js');
            $this->initialized = true;
        }
    }

    /**
     * Returns the configuration array
     * (global and instance specific settings are merged into one array).
     *
     * @param array $config The specific configurations
     * @param array $events Event listeners for editor instance
     * @return array
     */
    protected function configSettings($config = [], $events = [])
    {
        // Set language
        if (!isset($config['language'])) {
            $config['language'] = $this->canonizeLanguage(Pi::service('i18n')->getLocale());
        }

        foreach ($events as $eventName => $handlers) {
            if (empty($handlers)) {
                continue;
            } elseif (count($handlers) == 1) {
                $config['on'][$eventName] = '@@' . $handlers[0];
            } else {
                $config['on'][$eventName] = '@@function (ev){';
                foreach ($handlers as $handler => $code) {
                    $config['on'][$eventName] .= '(' . $code . ')(ev);';
                }
                $config['on'][$eventName] .= '}';
            }
        }

        return $config;
    }

    /**
     * Render global event handlers
     *
     * @param array $globalEvents
     *
     * @return string
     */
    protected function renderGlobalEvents(array $globalEvents = [])
    {
        $out = '';

        foreach ($globalEvents as $eventName => $handlers) {
            foreach ($handlers as $handler => $code) {
                if (!isset($this->renderedGlobalEvents[$eventName])) {
                    $this->renderedGlobalEvents[$eventName] = [];
                }
                // Return only new events
                if (!in_array($code, $this->renderedGlobalEvents[$eventName])) {
                    $out                                      .= ($code ? PHP_EOL : '') . 'CKEDITOR.on("' . $eventName . '", {$code});';
                    $this->renderedGlobalEvents[$eventName][] = $code;
                }
            }
        }

        return $out;
    }

    /**
     * Canonize language
     *
     * @param string $language
     *
     * @return string
     */
    protected function canonizeLanguage($language)
    {
        $language = strtolower(str_replace('_', '-', $language));

        return $language;
    }
}
