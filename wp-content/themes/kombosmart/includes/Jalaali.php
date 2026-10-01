<?php

namespace Jalaali;

/**
 * Jalaali PHP Implementation based on https://github.com/jalaali/jalaali-js
 */
class Jalaali
{
	public static function toJalaali($gy, $gm, $gd)
	{
		return self::d2j(self::g2d($gy, $gm, $gd));
	}

	public static function toGregorian($jy, $jm, $jd)
	{
		return self::d2g(self::j2d($jy, $jm, $jd));
	}

	public static function isValidJalaaliDate($jy, $jm, $jd)
	{
		return $jy >= -61 && $jy <= 3177
			&& $jm >= 1 && $jm <= 12
			&& $jd >= 1 && $jd <= self::jalaaliMonthLength($jy, $jm);
	}

	public static function isLeapJalaaliYear($jy)
	{
		return self::jalaaliCalendar($jy)['leap'] === 0;
	}

	public static function jalaaliMonthLength($jy, $jm)
	{
		if ($jm <= 6) return 31;
		if ($jm <= 11) return 30;
		return self::isLeapJalaaliYear($jy) ? 30 : 29;
	}

	public static function jalaaliCalendar($jy)
	{
		$breaks = [-61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210, 1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178];
		$bl     = count($breaks);
		$gy     = $jy + 621;
		$leapJ  = -14;
		$jp     = $breaks[0];

		if ($jy < $jp || $jy >= $breaks[$bl - 1]) {
			throw new \Exception('Invalid Jalaali Year: ' . $jy);
		}

		for ($i = 1; $i < $bl; $i++) {
			$jm   = $breaks[$i];
			$jump = $jm - $jp;
			if ($jy < $jm) break;
			$leapJ = $leapJ + self::div($jump, 33) * 8 + self::div(self::mod($jump, 33), 4);
			$jp    = $jm;
		}
		$n = $jy - $jp;

		$leapJ = $leapJ + self::div($n, 33) * 8 + self::div(self::mod($n, 33) + 3, 4);
		if (self::mod($jump, 33) === 4 && ($jump - $n) === 4) {
			$leapJ += 1;
		}

		$leapG = self::div($gy, 4) - self::div((self::div($gy, 100) + 1) * 3, 4) - 150;
		$march = 20 + $leapJ - $leapG;

		if ($jump - $n < 6) {
			$n = $n - $jump + self::div($jump + 4, 33) * 33;
		}
		$leap = self::mod(self::mod($n + 1, 33) - 1, 4);
		if ($leap === -1) $leap = 4;

		return ['leap' => $leap, 'gy' => $gy, 'march' => $march];
	}

	public static function j2d($jy, $jm, $jd)
	{
		$r = self::jalaaliCalendar($jy);
		return self::g2d($r['gy'], 3, $r['march']) + ($jm - 1) * 31 - self::div($jm, 7) * ($jm - 7) + $jd - 1;
	}

	public static function d2j($jdn)
	{
		$r  = self::d2g($jdn);
		$jy = $r['gy'] - 621;
		$r  = self::jalaaliCalendar($jy);
		$jdn1f = self::g2d($r['gy'], 3, $r['march']);
		$k  = $jdn - $jdn1f;

		if ($k >= 0) {
			if ($k <= 185) {
				return ['jy' => $jy, 'jm' => self::div($k, 31) + 1, 'jd' => self::mod($k, 31) + 1];
			}
			$k -= 186;
		} else {
			$jy -= 1;
			$k  += 179;
			if ($r['leap'] === 1) $k += 1;
		}

		return ['jy' => $jy, 'jm' => self::div($k, 30) + 7, 'jd' => self::mod($k, 30) + 1];
	}

	public static function g2d($gy, $gm, $gd)
	{
		$jdn = self::div(($gy + self::div($gm - 8, 6) + 100100) * 1461, 4)
			+ self::div(153 * self::mod($gm + 9, 12) + 2, 5)
			+ $gd - 34840408;
		return $jdn - self::div(self::div($gy + 100100 + self::div($gm - 8, 6), 100) * 3, 4) + 752;
	}

	public static function d2g($jdn)
	{
		$j  = 4 * $jdn + 139361631 + self::div(self::div(4 * $jdn + 183187720, 146097) * 3, 4) * 4 - 3908;
		$i  = self::div(self::mod($j, 1461), 4) * 5 + 308;
		$gd = self::div(self::mod($i, 153), 5) + 1;
		$gm = self::mod(self::div($i, 153), 12) + 1;
		$gy = self::div($j, 1461) - 100100 + self::div(8 - $gm, 6);
		return ['gy' => $gy, 'gm' => $gm, 'gd' => $gd];
	}

	private static function div($a, $b)
	{
		return (int)($a / $b);
	}

	private static function mod($a, $b)
	{
		return $a - (int)($a / $b) * $b;
	}
}
