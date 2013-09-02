<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Message;
use Pi;

/**
 * Public function for message module
 *
 * @author Xingyu Ji <xingyu@eefocus.com>
 */
class Service
{
    /**
     * Message summary
     *
     * @param  string $message
     * @param  int    $length
     * @return string
     */
    public static function messageSummary($message, $length = 40)
    {
        $encoding = Pi::service('i18n')->getCharset();
        $message = trim($message);

        if ($length && strlen($message) > $length) {
            $wordscut = '';
            if (strtolower($encoding) == 'utf-8') {
                $n = 0;
                $tn = 0;
                $noc = 0;
                while ($n < strlen($message)) {
                    $t = ord($message[$n]);
                    if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                        $tn = 1;
                        $n++;
                        $noc++;
                    } elseif (194 <= $t && $t <= 223) {
                        $tn = 2;
                        $n += 2;
                        $noc += 2;
                    } elseif (224 <= $t && $t < 239) {
                        $tn = 3;
                        $n += 3;
                        $noc += 2;
                    } elseif (240 <= $t && $t <= 247) {
                        $tn = 4;
                        $n += 4;
                        $noc += 2;
                    } elseif (248 <= $t && $t <= 251) {
                        $tn = 5;
                        $n += 5;
                        $noc += 2;
                    } elseif ($t == 252 || $t == 253) {
                        $tn = 6;
                        $n += 6;
                        $noc += 2;
                    } else {
                        $n++;
                    }
                    if ($noc >= $length) {
                        break;
                    }
                }
                if ($noc > $length) {
                    $n -= $tn;
                }
                $wordscut = substr($message, 0, $n);
            } else {
                for ($i = 0; $i < $length - 1; $i++) {
                    if (ord($message[$i]) > 127) {
                        $wordscut .= $message[$i] . $message[$i + 1];
                        $i++;
                    } else {
                        $wordscut .= $message[$i];
                    }
                }
            }
            $message = $wordscut . '...';
        }

        return trim($message);
    }
}
