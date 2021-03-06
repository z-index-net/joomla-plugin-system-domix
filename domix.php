<?php
/**
 * @author     mediahof, Kiel-Germany
 * @link       http://www.mediahof.de
 * @copyright  Copyright (C) 2011 - 2014 mediahof. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

abstract class domix
{

    public static $count = 0;

    public static $clean = false;

    private static $css = array(
        'border-top:  1px solid #00f;',
        'font-size:   11px;',
        'font-family: Verdana;',
        'color:       #000 !important;',
        'background:  #ffffef;'
    );

    public static function _(&$data, $count, $exit = false)
    {
        $type = gettype($data);
        $function = 'from' . ucfirst($type);
        $funccall = is_callable(array('self', $function)) ? $function : 'fromString';
        $out = call_user_func(array('self', $funccall), $data);

        if ($exit) {
            JResponse::clearHeaders();
            JResponse::setHeader('Content-Type', 'text/html; charset=utf-8');
            JResponse::sendHeaders();
        }

        if (!self::$clean) {
            echo '<pre style="' . implode(self::$css) . '"><u>' . $count . ':</u>' . "\n" . $out . "\n" . '</pre>';
        } else {
            echo htmlspecialchars_decode(strip_tags($out));
        }

        if ($exit) {
            exit;
        }
    }

    private static function fromObject($data)
    {
        $echo = array();
        $echo[] = 'instance of "' . get_class($data) . '" ';
        $parentclass = get_parent_class($data);
        $echo[] = $parentclass ? ' inherited from "' . $parentclass . '"' : '';
        $echo[] = "\n<u>Object variables</u>:\n";
        $echo[] = htmlspecialchars(print_r($data, true));
        $echo[] = "\n<u>Objekt methods</u>:\n";
        $echo[] = '- ' . print_r(implode("\n- ", get_class_methods(get_class($data))), true);
        if ($parentclass) {
            $echo[] = "\n<u>Public methods from " . $parentclass . "</u>:\n";
            $echo[] = '- ' . print_r(implode("\n- ", get_class_methods($parentclass)), true);
        }
        return implode($echo);
    }

    private static function fromBoolean($data)
    {
        return self::fromString($data);
    }

    private static function fromFloat($data)
    {
        return self::fromString($data);
    }

    private static function fromInteger($data)
    {
        return self::fromString($data);
    }

    private static function fromDouble($data)
    {
        return self::fromString($data);
    }

    private static function fromString($data)
    {
        return htmlspecialchars((string)$data);
    }

    private static function fromArray($data)
    {
        return print_r($data, true);
    }

    private static function fromResource($data)
    {
        return self::fromString($data);
    }

    public static function err()
    {
        if (self::allowed()) {
            error_reporting(6135);
            ini_set('display_errors', 1);
        }
    }

    public static function counter()
    {
        return ++self::$count;
    }

    public static function params()
    {
        static $params;
        if (!$params) {
            $plugin = JPluginHelper::getPlugin('system', 'domix');
            $params = new JRegistry($plugin->params);
        }
        return $params;
    }

    public static function allowed()
    {
        $ips = self::params()->get('ips');
        $allowed = array_filter(explode(PHP_EOL, $ips));

        if (empty($allowed)) {
            $allowed = array($ips);
        }

        $allowed = array_filter(array_map('trim', $allowed));

        foreach ($allowed as $allow) {
            if (filter_var($allow, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && self::ipv4($allow)) {
                return true;
            } elseif (self::ipv6($allow)) {
                return true;
            }
        }

        return false;
    }

    public static function ipv4($allow)
    {
        if ($allow == $_SERVER['REMOTE_ADDR']) {
            return true;
        }

        if (strpos($allow, '/') !== false) {
            list ($subnet, $bits) = explode('/', $allow);
            $ip = ip2long($_SERVER['REMOTE_ADDR']);
            $subnet = ip2long($subnet);
            $mask = -1 << (32 - $bits);
            $subnet &= $mask;
            return ($ip & $mask) == $subnet;
        }

        return false;
    }

    public static function ipv6($allow)
    {
        if ($_SERVER['REMOTE_ADDR'] == $allow) {
            return true;
        }

        if (strpos($allow, '/') !== false) {
            list($subnet, $mask) = explode('/', $allow);
            $subnet = inet_pton($subnet);
            $ip_pton = inet_pton($_SERVER['REMOTE_ADDR']);
            return self::iPv6CidrMatch($ip_pton, $subnet, $mask) ? true : false;
        }

        return false;
    }

    private static function iPv6MaskToByteArray($subnetMask)
    {
        $addr = str_repeat("f", $subnetMask / 4);
        switch ($subnetMask % 4) {
            case 0:
                break;
            case 1:
                $addr .= "8";
                break;
            case 2:
                $addr .= "c";
                break;
            case 3:
                $addr .= "e";
                break;
        }
        $addr = str_pad($addr, 32, '0');
        $addr = pack("H*", $addr);
        return $addr;
    }

    private static function iPv6CidrMatch($address, $subnetAddress, $subnetMask)
    {
        $binMask = self::iPv6MaskToByteArray($subnetMask);
        return ($address & $binMask) == $subnetAddress;
    }
}

/**
 * print given data for debugging
 *
 * @param mixed $data
 * @param bool $exit
 */
function domix($data, $exit = false)
{
    if (!domix::allowed()) {
        return;
    }

    $count = domix::counter();
    domix::_($data, 'Row ' . $count, $exit);
}

/**
 * domix with var_dump
 *
 * @param mixed $data
 * @param bool $exit
 */
function domixD($data, $exit = false)
{
    if (!domix::allowed()) {
        return;
    }

    if (function_exists('xdebug_get_code_coverage')) {
        domix::$clean = true;
    }

    $count = domix::counter();
    ob_start();
    var_dump($data);
    $data = ob_get_clean();
    domix::_($data, 'Dump ' . $count, $exit);

    if (domix::$clean == true) {
        domix::$clean = false;
    }
}

/**
 * send domix output via email
 *
 * @param mixed $data
 */
function domixM($data)
{
    $count = domix::counter();
    ob_start();
    domix::_($data, 'Row ' . $count);
    $body = ob_get_clean();
    $frommail = JFactory::getConfig()->get('mailfrom');
    $fromname = JFactory::getConfig()->get('fromname');

    $params = domix::params();
    $recipient = $params->get('mail');
    $subject = JFactory::getConfig()->get('sitename') . ' - domix ' . date('y.m.d H:i:s (u)');
    JMail::getInstance()->sendMail($frommail, $fromname, $recipient, $subject, $body, true);
}

/**
 * Save domix output to file
 *
 * @param mixed $data
 * @param bool $output exit with a hyper link to html file
 */
function domixF($data, $output = false)
{
    if (!domix::allowed()) {
        return;
    }

    $count = domix::counter();
    ob_start();
    domix::_($data, 'Row ' . $count);
    $body = ob_get_clean();
    $target = JPATH_ROOT . '/tmp' . DS . 'domix_' . date('y-m-d_H-i-s_u') . '.html';
    file_put_contents($target, $body);
    if ($output) {
        exit('<a href="' . $target . '">' . $target . '</a>');
    }
}

/**
 * results the current query (should be used after $db->loadResult(), $db->execute() or else
 * also output the sql error message if exists
 * @param bool $exit
 */
function domixDB($exit = false)
{
    $db = JFactory::getDBO();

    // replace prefix for copy/paste
    $search[] = '#__';
    $replace[] = $db->getPrefix();

    // new lines if multiple queries
    $search[] = ';';
    $replace[] = PHP_EOL . PHP_EOL . ';';

    // new line on each AND select statement
    $search[] = 'AND';
    $replace[] = PHP_EOL . 'AND';

    $query = str_ireplace($search, $replace, (string)$db->getQuery());

    if ($error = $db->getErrorMsg()) {
        $query = $query . PHP_EOL . PHP_EOL . $error;
    }

    domix($query, $exit);
}

/**
 * display function call trace
 * @param bool $exit
 */
function domixCT($exit = false)
{
    $e = new Exception();
    $trace = explode(PHP_EOL, $e->getTraceAsString());
    array_shift($trace); // remove {main}
    array_pop($trace); // remove call to this method
    $result = array();

    for ($i = 0; $i < count($trace); $i++) {
        $result[] = str_replace(JPATH_ROOT, '', substr($trace[$i], strpos($trace[$i], ' ')));
    }

    domix($result, $exit);
}