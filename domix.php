<?php
/**
 * DomiX Debug Static Class
 * 
 * @version    $Id$
 * @package    domix_debug_class
 * @author     Dominik Gorczyca, mediahof, Kiel-Germany
 * @copyright  Copyright (C) 2008 - 2011 mediahof. All rights reserved.
 * @license    GNU Public License <http://www.gnu.org/licenses/gpl.html>
 * @link       http://www.mediahof.de
 */

defined('_JEXEC') or die();

class domix{
    
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
}

/**
 * Gibt Daten mit domix aus
 *
 * @param mixed $data
 * @param bool $exit
 */
function domix( $data, $exit = false ){
	$count = domix::counter();
    domix::_( $data, 'Row '.$count, $exit );
}

/**
 * Gibt Daten mit domix und Firebug aus
 *
 * @param mixed $data
 * @param bool $exit
 */
function domixf( $data, $exit = false ){
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
function domixd( $data, $exit = false ){
	$count = domix::counter();
    ob_start();
    var_dump($data);
    $data = ob_get_clean();
    domix::_( $data, 'Dump '.$count, $exit );
}
