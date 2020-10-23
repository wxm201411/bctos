<?php
class MakeCode {
	private static $element = array (
			'0',
			'1',
			'2',
			'3',
			'4',
			'5',
			'6',
			'7',
			'8',
			'9',
			'A',
			'B',
			'C',
			'D',
			'E',
			'F',
			'G',
			'H',
			'I',
			'J',
			'K',
			'L',
			'M',
			'N',
			'O',
			'P',
			'Q',
			'R',
			'S',
			'T',
			'U',
			'V',
			'W',
			'Y',
			'Z' 
	);
	private static $hex_min = 2;
	private static $hex_max = 35;
	
	/**
	 * 进制转换
	 */
	public function conv($int, $out_hex, $in_hex = 10, $lenght = 8) {
		$hex_10 = $this->_conv2hex10 ( $int, $in_hex );
		$code = 'X' . strtoupper ( $this->_conv_hex ( $hex_10, $out_hex ) );
		$len = strlen ( $code );
		$rand_len = $lenght - $len;
		if ($rand_len > 0) {
			for($i = 0; $i < $rand_len; $i ++) {
				$rand = rand ( 0, 34 );
				$code = self::$element [$rand] . $code;
			}
		}
		
		return $code;
	}
	
	/**
	 * 将任意进制数字转为10进制数字
	 */
	private function _conv2hex10($int, $in_hex) {
		$int = strtoupper ( trim ( $int ) );
		if ($in_hex == 10) {
			return $int;
		}
		$array = array ();
		$result = 0;
		for($i = 0; $i < strlen ( $int ); $i ++) {
			array_unshift ( $array, substr ( $int, $i, 1 ) ); // 插入到数组头部（既倒序）
		}
		foreach ( $array as $k => $v ) {
			
			$hex10_value = array_search ( $v, self::$element );
			if ($hex10_value == - 1) {
				return false;
			}
			$result += intval ( pow ( $in_hex, $k ) * $hex10_value );
		}
		return $result;
	}
	
	/**
	 * 把10进制数换成任意进制数
	 */
	private function _conv_hex($hex_10, $out_hex) {
		$hex_10 = intval ( $hex_10 );
		
		if ($out_hex == 10) {
			return $hex_10;
		}
		
		$array = array ();
		$result = "";
		
		// 利用10进制数除任意进制数 倒取余数法转换。
		do {
			array_unshift ( $array, $hex_10 % $out_hex ); // 余数插入到数组数组第1个位置。
			$hex_10 = $hex_10 / $out_hex; // 除法
		} while ( $hex_10 > 1 );
		
		foreach ( $array as $k ) {
			$result .= self::$element [$k];
		}
		return $result;
	}
}