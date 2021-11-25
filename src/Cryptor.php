<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Util;

/**
 * Copied from https://github.com/ioncube/php-openssl-cryptor/blob/master/src/Cryptor.php to prevent including yet another library and possibly modify to our needs.
 *
 * Simple example of using the openssl encrypt/decrypt functions that
 * are inadequately documented in the PHP manual.
 *
 * Available under the MIT License
 *
 * The MIT License (MIT)
 * Copyright (c) 2016 ionCube Ltd.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of
 * the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO
 * THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
 * OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT
 * OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
class Cryptor
{
    /**
     * @var int
     */
    const
        FORMAT_RAW = 0,
        FORMAT_B64 = 1,
        FORMAT_HEX = 2;

    /**
     * @var string
     */
    protected $cipherAlgorithm;

    /**
     * @var string
     */
    protected $hashAlgorithm;

    /**
     * @var int
     */
    protected $format;

    /**
     * @var int
     */
    private $iVNumberOfBytes;

    /**
     * @var string
     */
    private $secret;

    /**
     * @param string $secret
     */
    public function __construct(string $secret)
    {
        $this->cipherAlgorithm = 'aes-256-ctr';
        $this->hashAlgorithm = 'sha256';
        $this->format = self::FORMAT_B64;
        $this->iVNumberOfBytes = openssl_cipher_iv_length($this->cipherAlgorithm);
        $this->secret = $secret;
    }


    /**
     * @param string $in
     * @return string
     * @throws \Exception
     */
    public function encryptString($in)
    {
        // Build an initialisation vector
        $iv = openssl_random_pseudo_bytes($this->iVNumberOfBytes, $isStrongCrypto);
        if (!$isStrongCrypto) {
            throw new \Exception('Not a strong key');
        }

        // Hash the key
        $keyhash = openssl_digest($this->secret, $this->hashAlgorithm, true);

        // and encrypt
        $opts = OPENSSL_RAW_DATA;
        $encrypted = openssl_encrypt($in, $this->cipherAlgorithm, $keyhash, $opts, $iv);

        if (false === $encrypted) {
            throw new \Exception(sprintf('Encryption failed: %s', openssl_error_string()));
        }

        // The result comprises the IV and encrypted data
        $res = $iv . $encrypted;

        // and format the result if required.
        if ($this->format == Cryptor::FORMAT_B64) {
            $res = base64_encode($res);
        } elseif ($this->format == Cryptor::FORMAT_HEX) {
            $res = unpack('H*', $res)[1];
        }
        return $res;
    }

    /**
     * @param string $in
     * @return string
     * @throws \Exception
     */
    public function decryptString($in)
    {
        $raw = $in;

        // Restore the encrypted data if encoded
        if ($this->format == Cryptor::FORMAT_B64) {
            $raw = base64_decode($in);
        } elseif ($this->format == Cryptor::FORMAT_HEX) {
            $raw = pack('H*', $in);
        }

        // and do an integrity check on the size.
        if (strlen($raw) < $this->iVNumberOfBytes) {
            throw new \Exception(sprintf('data length %d is less than iv length of %d', strlen($raw), $this->iVNumberOfBytes));
        }

        // Extract the initialisation vector and encrypted data
        $iv = substr($raw, 0, $this->iVNumberOfBytes);
        $raw = substr($raw, $this->iVNumberOfBytes);

        // Hash the key
        $keyhash = openssl_digest($this->secret, $this->hashAlgorithm, true);

        // and decrypt.
        $opts = OPENSSL_RAW_DATA;
        $res = openssl_decrypt($raw, $this->cipherAlgorithm, $keyhash, $opts, $iv);

        if ($res === false) {
            throw new \Exception(sprintf('decryption failed: %s', openssl_error_string()));
        }
        return $res;
    }
}
