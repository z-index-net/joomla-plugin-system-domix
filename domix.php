<?php
/**
 * DomiX Debug Static Class
 * 
 * @version    $Id$
 * @package    domix_debug_class
 * @author     Dominik Gorczyca, mediahof, Kiel-Germany
 * @copyright  Copyright (C) 2008 - 2012 mediahof. All rights reserved.
 * @license    GNU Public License <http://www.gnu.org/licenses/gpl.html>
 * @link       http://www.mediahof.de
 */

defined('_JEXEC') or die();

abstract class domix{
    
    public static $count = 0;

	private static $_css = array( 
		'border-top:  1px solid #00f;',
		'font-size:   11px;',
		'font-family: Verdana;',
		'color:       #000 !important;',
		'background:  #ffffef;' 
	);
	
	public static function _( &$data, $count, $exit = false){
		$type = gettype( $data );
		$function = 'from' .ucfirst( $type );
		$funccall = is_callable( 'self::' .$function ) ? $function : 'fromString';
		$out = call_user_func( array( 
			'self', $funccall 
		), $data );

		echo '<pre style="'.implode(self::$_css).'"><u>'.$count.':</u>'."\n".$out."\n".'</pre>';

		if ( $exit ) exit();
	}
    
	private static function fromObject( $data ){
		$echo = array();
		$echo[] = 'Instanz von "'.get_class( $data ).'" ';
		$parentclass = get_parent_class( $data );
		$echo[] = $parentclass ? ' geerbt von "'.$parentclass.'"' : '';
		$echo[] = "\n<u>Objekt Variablen</u>:\n";
		$echo[] = htmlspecialchars(print_r( $data, true ));
		$echo[] = "\n<u>Objekt Methoden</u>:\n";
		$echo[] = '- '.print_r( implode( "\n- ", get_class_methods( get_class( $data ) ) ), true );
		if($parentclass) {
			$echo[] = "\n<u>Public Methoden von " .$parentclass ."</u>:\n";
			$echo[] = '- '.print_r( implode( "\n- ", get_class_methods( $parentclass ) ), true );
		}
		return implode( $echo );
	}
	
	private static function fromBoolean( $data ){
		return self::fromString( $data );
	}
	
	private static function fromFloat( $data ){
		return self::fromString( $data );
	}
	
	private static function fromInteger( $data ){
		return self::fromString( $data );
	}
	
	private static function fromDouble( $data ){
		return self::fromString( $data );
	}
	
	private static function fromString( $data ){
		return htmlspecialchars( ( string ) $data);
	}
	
	private static function fromArray( $data ){
		return print_r( $data, true );
	}
	
	private static function fromResource( $data ){
		return self::fromString( $data );
	}
    
    public function counter() {
        return ++self::$count;
    }
    
    public function allowed() {
        return (self::params()->get('ip') == $_SERVER['REMOTE_ADDR']);
    }
    
    public function params() {
        static $params;
        if(!$params) {
            $plugin = &JPluginHelper::getPlugin('system', 'domix');
            $params = new JParameter($plugin->params);
        }
        return $params;
    }
}

/**
 * Gibt Daten mit domix aus
 *
 * @param mixed $data
 * @param bool $exit
 */
function domix( $data, $exit = false ){
    if(!domix::allowed()) { return; }

	$count = domix::counter();
    domix::_( $data, 'Row '.$count, $exit );
}

/**
 * Gibt Daten mit domix und Firebug aus
 *
 * @param mixed $data
 * @param bool $exit
 */
function domixFB( $data, $exit = false ){
    if(!domix::allowed()) { return; }
    
    $count = domix::counter();
    fb($data, 'Row '.$count);
    domix::_( $data, 'Row '.$count, $exit );
}

/**
 * Gibt Daten mit domix und var_dump aus
 *
 * @param mixed $data
 * @param bool $exit
 */
function domixD( $data, $exit = false ){
    if(!domix::allowed()) { return; }

	$count = domix::counter();
    ob_start();
    var_dump($data);
    $data = ob_get_clean();
    domix::_( $data, 'Dump '.$count, $exit );
}

/**
 * Gibt Daten mit domix aus und sendet diese per mail
 *
 * @param mixed $data
 * @param bool $exit
 */
function domixM( $data ){
    if(!domix::allowed()) { return; }

	$count = domix::counter();
    ob_start();
    domix::_( $data, 'Row '.$count);
    $body = ob_get_clean();
    $frommail = & JFactory::getConfig()->getValue('config.mailfrom');
    $fromname = & JFactory::getConfig()->getValue('config.fromname');
    
    $params = &domix::params();
    $recipient = $params->get('mail');
    if($recipient){
        $subject = JFactory::getConfig()->getValue('config.sitename') . ' - domix ' . date('y.m.d h:i:s');
        JUtility::sendMail($frommail, $fromname, $recipient, $subject, $body, true);
    }else{
        JError::raiseWarning(404, 'domixM not work, no recipient set');
    }
}


/**
 * Speichert die Domix Ausgabe als HTML Datei
 *
 * @param mixed $data
 * @param bool $exit
 */
function domixF( $data, $output=false ){
    if(!domix::allowed()) { return; }

	$count = domix::counter();
    ob_start();
    domix::_( $data, 'Row '.$count);
    $body = ob_get_clean();
    $target = 'tmp' . DS . 'domix_' . date('y-m-d_h-i-s') . '.html';
    file_put_contents(JPATH_ROOT . DS . $target, $body);
    if($output) {
        exit('<a href="'.$target.'">'.$target.'</a>');
    }
}

/**
 * Gibt den aktuellen Query und einen dazugehörigen Fehler aus.
 */
function domixDB(){
    $db = & JFactory::getDBO();
    $query = $db->getQuery();
    $error = $db->getErrorMsg();
    if($error) {
        $data = str_replace(';', PHP_EOL, ($query . PHP_EOL . PHP_EOL . $error));
        domix::_($data , 'SQL Error');
    }else{
        domix($query);
    }
}

