<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Laminas\View\Helper\HeadScript as LaminasHeadScript;

/**
 * Helper for setting and retrieving script elements for HTML head section
 *
 * Note: `defer` attribute is enabled by default for JavaScript.
 * To disable it, specify the attribute explicitly `'defer' => false`
 *
 * A new use case with raw type content
 *
 * ```
 *  <...>
 *  <?php
 *  $this->headScript()->captureStart();
 *  ?>
 *  <...>
 *  <?php
 *  // Store with name of "MyScript"
 *  $this->headScript()->captureTo('MyScript');
 *  ?>
 *  <...>
 *  <?php
 *  $this->headScript()->captureStart();
 *  ?>
 *  <...>
 *  <?php
 *  // Content will be discarded since the name of "MyScript" already exists
 *  $this->headScript()->captureTo('MyScript');
 *  ?>
 * ```
 *
 * @see    \Laminas\View\Helper\HeadScript for details.
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class HeadScript extends LaminasHeadScript
{

    protected $optionalAttributes
        = [
            'charset',
            'crossorigin',
            'defer',
            'async',
            'language',
            'src',
        ];

    /**#@+
     * Added by Taiwen Jiang
     */
    /** @var string[] Segment names for capture */
    protected static $captureNames = [];
    /**#@-*/

    /**
     * {@inheritDoc}
     *
     * Handles `defer` attribute for JavaScript loading
     */
    public function itemToString($item, $indent, $escapeStart, $escapeEnd)
    {
        if (isset($item->attributes['defer']) && !$item->attributes['defer']) {
            unset($item->attributes['defer']);
        }

        $attrString = '';
        if (!empty($item->attributes)) {
            foreach ($item->attributes as $key => $value) {
                if ((!$this->arbitraryAttributesAllowed() && !in_array($key, $this->optionalAttributes))
                    || in_array($key, ['conditional', 'noescape'])
                ) {
                    continue;
                }
                if ('defer' == $key) {
                    $value = 'defer';
                }
                $attrString .= sprintf(' %s="%s"', $key, ($this->autoEscape) ? $this->escape($value) : $value);
            }
        }

        $addScriptEscape = !(isset($item->attributes['noescape'])
            && filter_var($item->attributes['noescape'], FILTER_VALIDATE_BOOLEAN));

        $type = ($this->autoEscape) ? $this->escape($item->type) : $item->type;

        if ($type == 'text/javascript') {
            $html = '<script ' . $attrString . '>';
        } else {
            $html = '<script type="' . $type . '"' . $attrString . '>';
        }

        if (!empty($item->source)) {
            $html .= PHP_EOL;

            if ($addScriptEscape) {
                $html .= $indent . '    ' . $escapeStart . PHP_EOL;
            }

            $html .= $indent . '    ' . $item->source;

            if ($addScriptEscape) {
                $html .= PHP_EOL . $indent . '    ' . $escapeEnd;
            }

            $html .= PHP_EOL . $indent;
        }
        $html .= '</script>';

        if (isset($item->attributes['conditional'])
            && !empty($item->attributes['conditional'])
            && is_string($item->attributes['conditional'])
        ) {
            // inner wrap with comment end and start if !IE
            if (str_replace(' ', '', $item->attributes['conditional']) === '!IE') {
                $html = '<!-->' . $html . '<!--';
            }
            $html = $indent . '<!--[if ' . $item->attributes['conditional'] . ']>' . $html . '<![endif]-->';
        } else {
            $html = $indent . $html;
        }

        return $html;
    }

    /**#@+
     * Added by Taiwen Jiang
     */
    /**
     * End capture action and store after checking against stored scripts.
     * The content will be discarded if content with the name already exists
     *
     * @param string $name
     *
     * @return void
     */
    public function captureTo($name)
    {
        // Skip the script segment if it is already captured
        if (in_array($name, static::$captureNames)) {
            ob_end_clean();
            $this->captureScriptType  = null;
            $this->captureScriptAttrs = null;
            $this->captureLock        = false;

            return;
        }
        static::$captureNames[] = $name;
        $this->captureEnd();
    }
    /**#@-*/

    /**
     * Retrieve string representation
     *
     * @param string|int $indent Amount of whitespaces or string to use for indention
     *
     * @return string
     */
    public function toString($indent = null)
    {
        $indent = (null !== $indent)
            ? $this->getWhitespace($indent)
            : $this->getIndent();

        if ($this->view) {
            $useCdata = $this->view->plugin('doctype')->isXhtml();
        } else {
            $useCdata = $this->useCdata;
        }

        $escapeStart = ($useCdata) ? '//<![CDATA[' : '//<!--';
        $escapeEnd   = ($useCdata) ? '//]]>' : '//-->';

        $items = [];
        $this->getContainer()->ksort();

        // Load general config
        $configGeneral = Pi::config('', 'system', 'general');

        if (Pi::engine()->section() == 'front' && $configGeneral['compile_js']) {
            $isUserSection      = new IsUserSection();
            $module             = Pi::service('module')->current();
            $isUserSectionValue = $isUserSection->__invoke($module);

            if (!$isUserSectionValue) {
                $assetsByHash = [];
                $baseUrl      = Pi::url();
                $basePath     = Pi::host()->path(null);

                foreach ($this->getContainer()->getArrayCopy() as $key => $item) {
                    if (!empty($item->type) && !empty($item->attributes['src']) && $item->type == 'text/javascript'
                        && preg_match(
                            '#' . $baseUrl . '#', $item->attributes['src']
                        )
                    ) {
                        $parts = parse_url($item->attributes['src']);

                        if (empty($parts['query'])) {
                            $parts['query'] = '';
                        }

                        $hash = md5($parts['path'] . $parts['query']);

                        $content = file_get_contents($basePath . str_replace($baseUrl, '', strtok($item->attributes['src'], '?')));

                        $deferHash = !empty($item->attributes['defer']) && $item->attributes['defer'] == 'defer' ? 'defer' : 'nodefer';

                        $assetsByHash[$deferHash][$hash] = $content . ";"; // add semicolon for keeping conflicts / wrong syntax for next script
                        $this->getContainer()->offsetUnset($key);
                    }
                }

                if ($assetsByHash) {
                    foreach ($assetsByHash as $defer => $assetsByHashDefer) {
                        $finalHash          = md5(implode('', array_keys($assetsByHashDefer)));
                        $compiledJsDirPath  = Pi::host()->path('asset/compiled/js');
                        $compiledJsDirUrl   = Pi::url('asset/compiled/js');
                        $compiledJsFilePath = $compiledJsDirPath . DIRECTORY_SEPARATOR . $finalHash . '.js';
                        $compiledJsFileUrl  = $compiledJsDirUrl . DIRECTORY_SEPARATOR . $finalHash . '.js';

                        if (!is_dir($compiledJsDirPath)) {
                            mkdir($compiledJsDirPath, 0777, true);
                        }

                        if (!file_exists($compiledJsFilePath)) {
                            file_put_contents($compiledJsFilePath, implode("\n\n\n", $assetsByHashDefer));
                        }

                        $jsObject                    = new \stdClass();
                        $jsObject->type              = 'text/javascript';
                        $jsObject->attributes['ext'] = 'js';
                        $jsObject->attributes['src'] = $compiledJsFileUrl;

                        if ($defer == 'defer') {

                            $jsObject->attributes['defer'] = 'defer';
                        }

                        $this->getContainer()->prepend($jsObject);
                    }
                }
            }

        }


        foreach ($this as $item) {
            if (!$this->isValid($item)) {
                continue;
            }

            $items[] = $this->itemToString($item, $indent, $escapeStart, $escapeEnd);
        }

        return implode($this->getSeparator(), $items);
    }
}
