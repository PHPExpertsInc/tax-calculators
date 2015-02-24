<?php
// This file is a part of the Thrive Framework, a PHPExperts.pro Project.
//
// Copyright (c) 2012 PHP Experts, Inc.
// Author: Theodore R.Smith (theodore@phpexperts.pro)
//         http://users.phpexperts.pro/tsmith/
// Provided by the PHP University (www.phpu.cc) and PHPExperts.pro (www.phpexperts.pro)
//
// This file is dually licensed under the terms of the following licenses:
// * Primary License: OSSAL v1.0 - Open Source Software Alliance License
//   * Key points:
//       5.Redistributions of source code in any non-textual form (i.e.
//          binary or object form, etc.) must not be linked to software that is
//          released with a license that requires disclosure of source code
//          (ex: the GPL).
//       6.Redistributions of source code must be licensed under more than one
//          license and must not have the terms of the OSSAL removed.
//   * See LICENSE.ossal for complete details.
//
// * Secondary License: Creative Commons Attribution License v3.0
//   * Key Points:
//       * You are free:
//           * to copy, distribute, display, and perform the work
//           * to make non-commercial or commercial use of the work in its original form
//       * Under the following conditions:
//           * Attribution. You must give the original author credit. You must retain all
//             Copyright notices and you must include the sentence, "Based upon work from
//             PHPExperts.pro (www.phpexperts.pro).", wherever you list contributors.
//   * See LICENSE.cc_by for complete details.

// TODO: Create Unit Tests
class Thrive_Number extends fNumber
{
	const DIGITS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_!"#$%&\'()*,/:;<=>?@[\]^`{|}ẠẮẰẶẤẦẨẬẼẸẾỀỂỄỆỐỒỔỖỘỢỚỜỞỊỎỌỈỦŨỤỲÕắằặấầẨậẽẹếềểễệốồổỗỠƠộờởịỰỨỪỬơớƯÀÁÂÃẢĂẳẵÈÉÊẺÌÍĨỳĐứÒÓÔạỷừửÙÚỹỵÝỡưàáâãảăữẫèéêẻìíĩỉđựòóôõỏọụùúũủýợ';
	public function convertToBase($toBase)
	{
		// can't handle numbers larger than 2^31-1 = 2147483647
		$number = (int)$this->__toString();
		$chars = self::DIGITS;
		$str = '';
		do {
			$i = $number % $toBase;
			$str = $chars[$i] . $str;
			$number = ($number - $i) / $toBase;
		} while ($number > 0);

		return $str;
	}

	/*
	 * TODO: Check this function out.
	 */
	/*
	function static intToAlphaBaseN($n,$baseArray) {
	    $l=count($baseArray);
	    $s = '';
	    for ($i = 1; $n >= 0 && $i < 10; $i++) {
	        $s =  $baseArray[($n % pow($l, $i) / pow($l, $i - 1))].$s;
	        $n -= pow($l, $i);
	    }
	    return $s;
	}
*/

	public function convertFromBase($fromBase)
	{
		$number = $this->__toString();
		$len = strlen($number);
		$value = 0;
		$chars = self::DIGITS;
		$arr = array_flip(str_split($chars));
		for ($i = 0; $i < $len; ++$i) {
			$value += $arr[$number[$i]] * pow($fromBase, $len-$i-1);
		}

		return $value;
	}

	// Author: JR
	// Obtained from http://www.php.net/manual/en/function.base-convert.php#105414
	// License: Public Domain
	public function toRomanNumerals()
	{
		$N = $this->value;

		$c='IVXLCDM';
		for($a=5,$b=$s='';$N;$b++,$a^=7)
			for($o=$N%$a,$N=$N/$a^0;$o--;$s=$c[$o>2?$b+$N-($N&=-2)+$o=1:$b].$s);
		return $s;
	}
}

//$converter = new Thrive_Number_BaseConverter;
//echo $converter->convertToBase10('3456789ABCDEF', 16) . "\n";