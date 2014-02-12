<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Elnur Abdurrakhimov <elnur@elnur.pro>
 * @author Terje Bråten <terje@braten.be>
 */
class Symfony_Component_Security_Core_Encoder_BCryptPasswordEncoder extends Symfony_Component_Security_Core_Encoder_BasePasswordEncoder
{
    /**
     * @var Symfony_Component_Security_Core_Util_SecureRandomInterface
     */
    private $secureRandom;

    /**
     * @var string
     */
    private $cost;

    private static $prefix = null;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Security_Core_Util_SecureRandomInterface $secureRandom A SecureRandomInterface instance
     * @param integer               $cost         The algorithmic cost that should be used
     *
     * @throws InvalidArgumentException if cost is out of range
     */
    public function __construct(Symfony_Component_Security_Core_Util_SecureRandomInterface $secureRandom, $cost)
    {
        if (!function_exists('password_hash') && !CRYPT_BLOWFISH) {
            throw new RuntimeException('To use the BCrypt encoder, you need to upgrade to PHP 5.5 or install the "ircmaxell/password-compat" via Composer.');
        }

        $this->secureRandom = $secureRandom;

        $cost = (int) $cost;
        if ($cost < 4 || $cost > 31) {
            throw new InvalidArgumentException('Cost must be in the range of 4-31.');
        }
        $this->cost = $cost;

        if (!self::$prefix) {
            self::$prefix = '$'.(version_compare(phpversion(), '5.3.7', '>=') ? '2y' : '2a').'$';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function encodePassword($raw, $salt)
    {
        if (function_exists('password_hash')) {
            $options = array('cost' => $this->cost);

            if ($salt) {
                $options['salt'] = $salt;
            }

            return password_hash($raw, PASSWORD_BCRYPT, $options);
        }

        $salt = self::$prefix.sprintf('%02d', $this->cost).'$'.$this->encodeSalt($this->getRawSalt());
        $encoded = crypt($raw, $salt);
        if (!is_string($encoded) || strlen($encoded) <= 13) {
            return false;
        }

        return $encoded;
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        if (function_exists('password_verify')) {
            return password_verify($raw, $encoded);
        }

        $crypted = crypt($raw, $encoded);
        if (strlen($crypted) <= 13) {
            return false;
        }

        return $this->comparePasswords($encoded, $crypted);
    }

    /**
     * Encodes the salt to be used by Bcrypt.
     *
     * The blowfish/bcrypt algorithm used by PHP crypt expects a different
     * set and order of characters than the usual base64_encode function.
     * Regular b64: ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/
     * Bcrypt b64:  ./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789
     * We care because the last character in our encoded string will
     * only represent 2 bits.  While two known implementations of
     * bcrypt will happily accept and correct a salt string which
     * has the 4 unused bits set to non-zero, we do not want to take
     * chances and we also do not want to waste an additional byte
     * of entropy.
     *
     * @param  bytes  $random a string of 16 random bytes
     *
     * @return string Properly encoded salt to use with php crypt function
     *
     * @throws InvalidArgumentException if string of random bytes is too short
     */
    protected function encodeSalt($random)
    {
        $len = strlen($random);
        if ($len < 16) {
            throw new InvalidArgumentException('The bcrypt salt needs 16 random bytes.');
        }
        if ($len > 16) {
            $random = substr($random, 0, 16);
        }

        $base64raw = str_replace('+', '.', base64_encode($random));
        $salt128bit = substr($base64raw, 0, 21);
        $lastchar = substr($base64raw, 21, 1);
        $lastchar = strtr($lastchar, 'AQgw', '.Oeu');
        $salt128bit .= $lastchar;

        return $salt128bit;
    }

    /**
     * @return bytes 16 random bytes to be used in the salt
     */
    protected function getRawSalt()
    {
        $rawSalt = false;
        $numBytes = 16;
        if (function_exists('mcrypt_create_iv')) {
            if ('\\' === DIRECTORY_SEPARATOR && version_compare(PHP_VERSION, '5.3.0', '<')) {
                $source = MCRYPT_RAND;
            } else {
                $source = MCRYPT_DEV_URANDOM;
            }

            $rawSalt = mcrypt_create_iv($numBytes, $source);
        }
        if (!$rawSalt) {
            $rawSalt = $this->secureRandom->nextBytes($numBytes);
        }

        return $rawSalt;
    }
}
