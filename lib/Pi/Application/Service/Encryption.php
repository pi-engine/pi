<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use phpseclib\Crypt\AES as CryptAES;
use phpseclib\Crypt\Rijndael as CryptRijndael;
use phpseclib\Crypt\Twofish as CryptTwofish;
use phpseclib\Crypt\Blowfish as CryptBlowfish;
use phpseclib\Crypt\RC4 as CryptRC4;
use phpseclib\Crypt\RC2 as CryptRC2;
use phpseclib\Crypt\TripleDES as CryptTripleDES;
use phpseclib\Crypt\DES as CryptDES;

/**
 * Encryption service, use phpseclib for encrypt and decrypt
 * more information, source, documents and examples : http://phpseclib.sourceforge.net/
 *
 * Pi::service('encryption')->process($string, $options, $type);
 *
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
class Encryption extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'encryption';

    /**
     * Encrypt string
     *
     * @param string $input
     * @param string $type
     * @param array  $options
     *
     * @return string $output
     */
    public function process($input, $type = 'encrypt', $options = [])
    {
        // Set options
        $options['method']       = isset($options['method']) ? $options['method'] : $this->options['method'];
        $options['key']          = isset($options['key']) ? $options['key'] : $this->options['key'];
        $options['vi']           = isset($options['vi']) ? $options['vi'] : $this->options['vi'];
        $options['length']       = isset($options['length']) ? $options['length'] : $this->options['length'];
        $options['block_length'] = isset($options['block_length']) ? $options['block_length'] : $this->options['block_length'];
        $options['mode']         = isset($options['mode']) ? $options['mode'] : '';

        // Start process
        switch ($options['method']) {
            case 'AES':
                return $this->AES($input, $options, $type);
                break;
            case 'Rijndael':
                return $this->Rijndael($input, $options, $type);
                break;
            case 'Twofish':
                return $this->Twofish($input, $options, $type);
                break;
            case 'Blowfish':
                return $this->Blowfish($input, $options, $type);
                break;
            case 'RC4':
                return $this->RC4($input, $options, $type);
                break;
            case 'RC2':
                return $this->RC2($input, $options, $type);
                break;
            case 'TripleDES':
                return $this->TripleDES($input, $options, $type);
                break;
            case 'DES':
                return $this->DES($input, $options, $type);
                break;
        }
    }

    /**
     * AES encryption
     *
     * @param string $input
     * @param string $type
     * @param array  $options
     *
     * @return string $output
     */
    protected function AES($input, $options, $type = 'encrypt')
    {
        // Encryption
        $cipher = new CryptAES($options['mode']);
        $cipher->setKey($options['key']);
        $cipher->setIV($options['vi']);
        $cipher->setKeyLength($options['length']);
        if ($type == 'decrypt') {
            $output = $cipher->decrypt(hex2bin($input));
        } else {
            $output = bin2hex($cipher->encrypt($input));
        }

        return $output;
    }

    /**
     * Rijndael encryption
     *
     * @param string $input
     * @param string $type
     * @param array  $options
     *
     * @return string $output
     */
    protected function Rijndael($input, $options, $type = 'encrypt')
    {
        // Encryption
        $cipher = new CryptRijndael($options['mode']);
        $cipher->setKey($options['key']);
        $cipher->setIV($options['vi']);
        $cipher->setKeyLength($options['length']);
        $cipher->setBlockLength($options['block_length']);
        if ($type == 'decrypt') {
            $output = $cipher->decrypt(hex2bin($input));
        } else {
            $output = bin2hex($cipher->encrypt($input));
        }

        return $output;
    }

    /**
     * Twofish encryption
     *
     * @param string $input
     * @param string $type
     * @param array  $options
     *
     * @return string $output
     */
    protected function Twofish($input, $options, $type = 'encrypt')
    {
        // Encryption
        $cipher = new CryptTwofish();
        $cipher->setKey($options['key']);
        if ($type == 'decrypt') {
            $output = $cipher->decrypt(hex2bin($input));
        } else {
            $output = bin2hex($cipher->encrypt($input));
        }

        return $output;
    }

    /**
     * Blowfish encryption
     *
     * @param string $input
     * @param string $type
     * @param array  $options
     *
     * @return string $output
     */
    protected function Blowfish($input, $options, $type = 'encrypt')
    {
        // Encryption
        $cipher = new CryptBlowfish();
        $cipher->setKey($options['key']);
        if ($type == 'decrypt') {
            $output = $cipher->decrypt(hex2bin($input));
        } else {
            $output = bin2hex($cipher->encrypt($input));
        }

        return $output;
    }

    /**
     * RC4 encryption
     *
     * @param string $input
     * @param string $type
     * @param array  $options
     *
     * @return string $output
     */
    protected function RC4($input, $options, $type = 'encrypt')
    {
        // Encryption
        $cipher = new CryptRC4();
        $cipher->setKey($options['key']);
        if ($type == 'decrypt') {
            $output = $cipher->decrypt(hex2bin($input));
        } else {
            $output = bin2hex($cipher->encrypt($input));
        }

        return $output;
    }

    /**
     * RC2 encryption
     *
     * @param string $input
     * @param string $type
     * @param array  $options
     *
     * @return string $output
     */
    protected function RC2($input, $options, $type = 'encrypt')
    {
        // Encryption
        $cipher = new CryptRC2();
        $cipher->setKey($options['key']);
        if ($type == 'decrypt') {
            $output = $cipher->decrypt(hex2bin($input));
        } else {
            $output = bin2hex($cipher->encrypt($input));
        }

        return $output;
    }

    /**
     * TripleDES encryption
     *
     * @param string $input
     * @param string $type
     * @param array  $options
     *
     * @return string $output
     */
    protected function TripleDES($input, $options, $type = 'encrypt')
    {
        // Encryption
        $cipher = new CryptTripleDES($options['mode']);
        $cipher->setKey($options['key']);
        $cipher->setIV($options['vi']);
        if ($type == 'decrypt') {
            $output = $cipher->decrypt(hex2bin($input));
        } else {
            $output = bin2hex($cipher->encrypt($input));
        }

        return $output;
    }

    /**
     * DES encryption
     *
     * @param string $input
     * @param string $type
     * @param array  $options
     *
     * @return string $output
     */
    protected function DES($input, $options, $type = 'encrypt')
    {
        // Encryption
        $cipher = new CryptDES($options['mode']);
        $cipher->setKey($options['key']);
        $cipher->setIV($options['vi']);
        if ($type == 'decrypt') {
            $output = $cipher->decrypt(hex2bin($input));
        } else {
            $output = bin2hex($cipher->encrypt($input));
        }

        return $output;
    }
}
