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
 * Http utility functions.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_HttpFoundation_IpUtils
{
    /**
     * This class should not be instantiated
     */
    private function __construct() {}

    /**
     * Validates an IPv4 or IPv6 address.
     *
     * @param string $requestIp
     * @param string $ip
     *
     * @return boolean Whether the IP is valid
     */
    public static function checkIp($requestIp, $ip)
    {
        if (false !== strpos($requestIp, ':')) {
            return self::checkIp6($requestIp, $ip);
        }

        return self::checkIp4($requestIp, $ip);
    }

    /**
     * Validates an IPv4 address.
     *
     * @param string $requestIp
     * @param string $ip
     *
     * @return boolean Whether the IP is valid
     */
    public static function checkIp4($requestIp, $ip)
    {
        if (false !== strpos($ip, '/')) {
            list($address, $netmask) = explode('/', $ip, 2);

            if ($netmask < 1 || $netmask > 32) {
                return false;
            }
        } else {
            $address = $ip;
            $netmask = 32;
        }

        return 0 === strncmp(sprintf('%032b', ip2long($requestIp)), sprintf('%032b', ip2long($address)), $netmask);
    }

    /**
     * Validates an IPv6 address.
     *
     * @author David Soria Parra <dsp at php dot net>
     * @see https://github.com/dsp/v6tools
     *
     * @param string $requestIp
     * @param string $ip
     *
     * @return boolean Whether the IP is valid
     *
     * @throws RuntimeException When IPV6 support is not enabled
     */
    public static function checkIp6($requestIp, $ip)
    {
        if (!(extension_loaded('sockets') && defined('AF_INET6')) || !(function_exists('inet_pton') && @inet_pton('::1'))) {
            throw new RuntimeException('Unable to check Ipv6. Check that PHP was not compiled with option "disable-ipv6".');
        }

        if (false !== strpos($ip, '/')) {
            list($address, $netmask) = explode('/', $ip, 2);

            if ($netmask < 1 || $netmask > 128) {
                return false;
            }
        } else {
            $address = $ip;
            $netmask = 128;
        }

        $bytesAddr = unpack("n*", inet_pton($address));
        $bytesTest = unpack("n*", inet_pton($requestIp));

        for ($i = 1, $ceil = ceil($netmask / 16); $i <= $ceil; $i++) {
            $left = $netmask - 16 * ($i-1);
            $left = ($left <= 16) ? $left : 16;
            $mask = ~(0xffff >> $left) & 0xffff;
            if (($bytesAddr[$i] & $mask) != ($bytesTest[$i] & $mask)) {
                return false;
            }
        }

        return true;
    }
}
