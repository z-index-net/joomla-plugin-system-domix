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
	private static $_css = array( 
		'border-top:  1px solid #00f;',
		'font-size:   11px;',
		'font-family: verdana;',
		'color:       #000;',
		'background:  #ffffef;' 
	);
	
	public static function _( &$data, $exit = false, $filename = false ){
		$type = gettype( $data );
		$function = 'from' .ucfirst( $type );
		$funccall = is_callable( 'self::' .$function ) ? $function : 'fromString';
		$out = call_user_func( array( 
			'self', $funccall 
		), $data );
		if ( $filename ) {
			$path = JPATH_SITE.DS.'domix';
			
			if(!file_exists($path)) {
				mkdir($path, 0777, true);
			}
			file_put_contents( $path.DS.$filename, $out );
		} else {
			echo '<pre style="'.implode(self::$_css).'">'."\n".$out."\n".'</pre>';
		}
		if ( $exit ) exit();
	}
	
	private static function fromObject( $data ){
		$echo = array();
		$echo[] = 'Instanz von "'.get_class( $data ).'" ';
		$parentclass = get_parent_class( $data );
		$echo[] = $parentclass ? ' geerbt von "'.$parentclass.'"' : '';
		$echo[] = "\nObjekt Variablen:\n";
		$echo[] = print_r( $data, true );
		$echo[] = "\nObjekt Methoden:\n";
		$echo[] = '- '.print_r( implode( "\n- ", get_class_methods( get_class( $data ) ) ), true );
		if($parentclass) {
			$echo[] = "\nPublic Methoden von " .$parentclass .":\n";
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
		return ( string ) $data;
	}
	
	private static function fromArray( $data ){
		return print_r( $data, true );
	}
	
	private static function fromResource( $data ){
		return self::fromString( $data );
	}
}

/**
 * Speichert Variablen in eine txt-Datei
 *
 * @param mixed $val
 * @param string $name
 */
function domix( &$val, $exit = false, $filename = false ){
	static $count = 0;
	$count++;
	if($filename) {
		domix::_( $val, $exit, $count.'_'.$filename.'.txt' );
	} else {
		domix::_( $count );
		domix::_( $val, $exit, false );
	}
}

