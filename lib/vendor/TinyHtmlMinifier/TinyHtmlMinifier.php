<?php
/**
 * https://github.com/jenstornell/tiny-html-minifier
 * Version 1.2
 */

namespace TinyHtmlMinifier;

class TinyHtmlMinifier {
    function __construct($options) {
        $this->options = $options;
        $this->elements = $this->elements();
        $this->elementsKeepSpace = $this->elementsKeepSpace();
        $this->elementsKeepAll = $this->elementsKeepAll();
        $this->elementsKeepNewlines = $this->elementsKeepNewlines();
    }

    function getOption($value) {
        $defaults = [
            'collapse_whitespace' => false
        ];
        if(isset($this->options[$value]))
            return $this->options[$value];
        return $defaults[$value];
    }

    // Minify
    function minify($html) {
        $html = str_replace("\r", '', $html);
        $html = $this->parser($html);
        
        return $html;
    }

    // Parser loop
    function parser($html) {
        $split = explode('<', $html);
        $html = '';

        foreach($split as $part) {
            if($part == '') continue;

            $name = $this->getName($part);
            $element = (count($split) > 1) ? $this->partToElement($part) : $part;

            $html .= $this->minifyHtml($name, $element);
        }

        return $html;
    }

    // Convert part to an element
    function partToElement($part) {
        return '<' . $part;
    }

    // Minify
    function minifyHtml($name, $element) {

        if($name == 'script' || $name == '!--'){
            $minifier = new \MatthiasMullie\Minify\JS($element);
            return $minifier->minify();
        } else {
            $element = $this->minifyEmptyName($name, $element);
            $element = $this->minifyElementClosed($name, $element);
            $element = $this->removeComment($name, $element);
            $element = $this->minifyElement($name, $element); // Head elements for most part
            $element = $this->minifyElementKeepSpace($name, $element); // Normal elements
            $element = $this->minifyElementKeepNewlines($name, $element); // Textarea, pre, code
        }


        return $element;
    }

    // Remove comment
    function removeComment($name, $element) {
        if(!$this->isComment($name)) return $element;

        $position = strpos($element, '-->');
        $element = substr($element, $position + 3);
        return $this->stripKeepSpace($element);
    }

    /* Is */

     // Is comment
     function isComment($name) {
        return ($name == '!--') ? true : false;
    }

    // Is style
    function isStyle($name) {
        return ($name == 'style') ? true : false;
    }

    // Is element closed
    function isElementClosed($name) {
        return (substr($name, 0, 1) == '/') ? true : false;
    }

    // Is element
    function isElement($name) {
        return (in_array($name, $this->elements)) ? true : false;
    }

     // Is element space
     function isElementKeepSpace($name) {
        return (in_array($name, $this->elementsKeepSpace)) ? true : false;
    }

    // Is element keep all
    function isElementKeepAll($name) {
        return (in_array($name, $this->elementsKeepAll)) ? true : false;
    }

    // Is element keep all
    function isElementKeepNewlines($name) {
        return (in_array($name, $this->elementsKeepNewlines)) ? true : false;
    }

    // Get name from element
    function getName($element) {
        $array = [
            ' ', '>', "\n", "\t"
        ];
        $length = 0;
        $string = '';
        foreach($array as $sep) {
            $split = $this->splitByDelimiter($sep, $element);
            $split_length = strlen($split);

            if($length == 0 || ($split_length != 0 && $split_length < $length)) {
                $length = $split_length;
                $string = $split;
            }
        }

        return strtolower($string);
    }

    // Limit string by a character
    function splitByDelimiter($delimiter, $element) {
        $position = strpos($element, $delimiter);
        $split = substr($element, 0, $position);
        return $split;
    }

    // Name to nicename
    function nicename($name) {
        $nicename = substr($name, 1);
        return $nicename;
    }

    /* Minify */

    // Minify keep newlines
    function minifyElementKeepNewlines($name, $element) {
        if($this->isElementKeepNewlines($name))
            return $this->stripKeepNewlines($element);
        return $element;
    }

    // Minify keep space
    function minifyElementKeepSpace($name, $element) {
        if($this->isElementKeepSpace($name)) {
            if($this->getOption('collapse_whitespace')) {
                return $this->strip($element);
            } else {
                return $this->stripKeepSpace($element);
            }
        }
        return $element;
    }

    // Minify element
    function minifyElement($name, $element) {
        if($this->isElement($name))
            return $this->strip($element);
        return $element;
    }

    // Minify empty name
    function minifyEmptyName($name, $element) {
        if($name == '')
            return trim($element);
        return $element;
    }

    // Minify element closed
    function minifyElementClosed($name, $element) {
        if($this->isElementClosed($name)) {
            $element = $this->minifyClosedElement($name, $element);
            $element = $this->minifyClosedElementKeepSpace($name, $element);
        }
        return $element;
    }

    // Minify closed element
    function minifyClosedElement($name, $element) {
        if($this->isElement($this->nicename($name)))
            return $this->strip($element);
        return $element;
    }

    // Minify closed element keep space
    function minifyClosedElementKeepSpace($name, $element) {
        $nicename = $this->nicename($name);
        if($this->isElementKeepSpace($nicename) || $this->isElementKeepAll($nicename)) {
            if($this->getOption('collapse_whitespace')) {
                return $this->strip($element);
            } else {
                return $this->stripKeepSpace($element);
            }
        }
        return $element;
    }

    /* Strip */

    // Strip as much as possible
    function strip($element) {
        $element = preg_replace('!\s+!', ' ', $element);
        $element = str_replace("> ", ">", $element);
        return trim($element);
    }

    // Strip but keep one space
    function stripKeepSpace($element) {
        return preg_replace('!\s+!', ' ', $element);
    }

    // Minify by preserving rows
    function stripKeepNewlines($element) {
        $rows = explode("\n", $element);
        $html = [];
        foreach($rows as $part) {
            $html[] = trim($part);

        }
        return implode("\n", $html);
    }

    /* Elements */

    // Elements to array
    function elementsToArray($elements) {
        $trim = str_replace(["\n", "\r"], ["", ""], $elements);
        return array_filter(explode(' ', $trim));
    }

    // Elements as rows
    function elementsKeepAll() {
        $elements = '
            code pre textarea
        ';
        return $this->elementsToArray($elements);
    }

    // Elements keep space
    function elementsKeepSpace() {
        $elements = '
            !doctype
            a abbr address area article aside audio
            b base bdi bdo blockquote body br button
            canvas caption cite col colgroup
            datalist dd del details dfn div dialog dl dt
            em embed
            fieldcaption fieldset figure footer form
            h1 h2 h3 h4 h5 h6
            hr head header html
            i img
            li
            main mark menu menuitem meta meter
            nav noscript
            object ol optgroup option output
            p param picture progress
            q
            rp rt ruby
            s samp span section select small source strong sub summery
            table tbody td tfoot th thead time title tr track
            u ul
            var vbr video
        ';
        return $this->elementsToArray($elements);
    }

    // Elements keep newlines
    function elementsKeepNewlines() {
        $elements = 'script';
        return $this->elementsToArray($elements);
    }

    // Elements
    function elements() {
        $elements = '
            !doctype body head html meta title link
        ';

        $elements .= '
            style
        ';

        $elements .= '
            svg path
        ';
        return $this->elementsToArray($elements);
    }
}