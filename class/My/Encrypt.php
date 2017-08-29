<?php

/* XXTEA encryption arithmetic library.
*
* Copyright (C) 2006 Ma Bingyao <andot@ujn.edu.cn>
* Version:      1.5
* LastModified: Dec 5, 2006
* This library is free.  You can redistribute it and/or modify it.

* changelog

	2010-06-22		change "+" to ".", "/" to ":", "=" to "," in safekey().
	2010-08-18		change ":" to "_" in safekey().
	2010-08-23		change "," to "-" in safekey().
*/

final class XXTEA{
	
	static public $key = '';
	static private $defaultKey = 'n839#4h_adh%53f02U1IxmP)d!^6y*Y';
	
	static public function setkey($key){
		if(empty($key)){
			if(!defined('ENCRYPT_KEY') || !ENCRYPT_KEY){
				self::$key = self::$defaultKey;
				//is_object($GLOBALS['myerror']) && $GLOBALS['myerror']->error(set($GLOBALS['words']['systemError']) ? $GLOBALS['words']['systemError'] : 'System configure Error!');
				//is_object($GLOBALS['myerror']) && $GLOBALS['myerror']->info('ENCRYPT_KEY was not Defined');
			}else{
				self::$key = ENCRYPT_KEY;
			}
		}elseif(strlen($key) > 16){
			self::$key = $key;
		}else{
			self::$key = self::$defaultKey;
		}
		return self::$key;
	}
	
	static private function safekey($string, $reverse = false){
		if ( $reverse === false ){
			return str_replace( array("/","+","="), array("_",".","-"), $string );
		}else{
			return str_replace( array("_",".","-"), array("/","+","="), $string );
		}
	}
	
	static private function long2str($v, $w) {
	 $len = count($v);
	 $n = ($len - 1) << 2;
	 if ($w) {
		 $m = $v[$len - 1];
		 if (($m < $n - 3) || ($m > $n)) return false;
		 $n = $m;
	 }
	 $s = array();
	 for ($i = 0; $i < $len; $i++) {
		 $s[$i] = pack("V", $v[$i]);
	 }
	 if ($w) {
		 return substr(join('', $s), 0, $n);
	 }
	 else {
		 return self::safekey(base64_encode(join('', $s)));
	 }
	}
	
	static private function str2long($s, $w) {
	 $v = unpack("V*", $s. str_repeat("\0", (4 - strlen($s) % 4) & 3));
	 $v = array_values($v);
	 if ($w) {
		 $v[count($v)] = strlen($s);
	 }
	 return $v;
	}
	
	static private function int32($n) {
	   while ($n >= 2147483648) $n -= 4294967296;
	   while ($n <= -2147483649) $n += 4294967296;
	   return (int)$n;
	}
	
	static public function encrypt($str, $key = '') {
	   if ($str == "") {
		   return "";
	   }
	   $key = self::setkey($key);
	   $v = self::str2long($str, true);
	   $k = self::str2long($key, false);
	   if (count($k) < 4) {
		   for ($i = count($k); $i < 4; $i++) {
			   $k[$i] = 0;
		   }
	   }
	   $n = count($v) - 1;
	
	   $z = $v[$n];
	   $y = $v[0];
	   $delta = 0x9E3779B9;
	   $q = floor(6 + 52 / ($n + 1));
	   $sum = 0;
	   while (0 < $q--) {
		   $sum = self::int32($sum + $delta);
		   $e = $sum >> 2 & 3;
		   for ($p = 0; $p < $n; $p++) {
			   $y = $v[$p + 1];
			   $mx = self::int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
			   $z = $v[$p] = self::int32($v[$p] + $mx);
		   }
		   $y = $v[0];
		   $mx = self::int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
		   $z = $v[$n] = self::int32($v[$n] + $mx);
	   }
	   return self::long2str($v, false);
	}
	
	static public function decrypt($str, $key = '') {
	   if ($str == "") {
		   return "";
	   }else{
		   $str = base64_decode(self::safekey($str, true));
	   }
	   $key = self::setkey($key);
	   $v = self::str2long($str, false);
	   $k = self::str2long($key, false);
	   if (count($k) < 4) {
		   for ($i = count($k); $i < 4; $i++) {
			   $k[$i] = 0;
		   }
	   }
	   $n = count($v) - 1;
	
	   $z = $v[$n];
	   $y = $v[0];
	   $delta = 0x9E3779B9;
	   $q = floor(6 + 52 / ($n + 1));
	   $sum = self::int32($q * $delta);
	   while ($sum != 0) {
		   $e = $sum >> 2 & 3;
		 for ($p = $n; $p > 0; $p--) {
			   $z = $v[$p - 1];
			  $mx = self::int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
			 $y = $v[$p] = self::int32($v[$p] - $mx);
		 }
		 $z = $v[$n];
		 $mx = self::int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
		  $y = $v[0] = self::int32($v[0] - $mx);
		  $sum = self::int32($sum - $delta);
	  }
	  return self::long2str($v, true);
	}
}


