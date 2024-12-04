<?php

namespace Kvnc\Services;

use Carbon\Carbon;

class HolidayChecker
{
	/**
	 * Get Turkish public holidays for a given year, including fixed and religious holidays.
	 *
	 * @param int|null $year
	 * @return array
	 */
	public static function getPublicHolidays(?int $year = null): array
	{
		$year = $year ?? (int) date('Y');

		// Fixed public holidays
		$fixedHolidays = [
			"$year-01-01", // New Year's Day
			"$year-04-23", // National Sovereignty and Children's Day
			"$year-05-01", // Labour and Solidarity Day
			"$year-05-19", // Commemoration of Atatürk, Youth and Sports Day
			"$year-07-15", // Democracy and National Unity Day
			"$year-08-30", // Victory Day
			"$year-10-29", // Republic Day
		];

		// Add religious holidays
		$religiousHolidays = self::getReligiousHolidays($year);

		return array_merge($fixedHolidays, $religiousHolidays);
	}

	/**
	 * Calculate religious holidays for Turkey for a given year.
	 *
	 * @param int $year
	 * @return array
	 */
	public static function getReligiousHolidays(int $year): array
	{
		$holidays = [];

		// Islamic dates for Eid al-Fitr (Ramazan Bayramı) and Eid al-Adha (Kurban Bayramı)
		$eidAlFitr = self::hijriToGregorian($year, 10, 1); // 1st of Shawwal
		$eidAlAdha = self::hijriToGregorian($year, 12, 10); // 10th of Dhu al-Hijjah

		// Add the start day and possible multi-day celebrations
		$holidays[] = $eidAlFitr->toDateString();
		$holidays[] = $eidAlFitr->addDay()->toDateString();
		$holidays[] = $eidAlFitr->addDay()->toDateString(); // Ramazan Bayramı: 3 days

		$holidays[] = $eidAlAdha->toDateString();
		$holidays[] = $eidAlAdha->addDay()->toDateString();
		$holidays[] = $eidAlAdha->addDay()->toDateString();
		$holidays[] = $eidAlAdha->addDay()->toDateString(); // Kurban Bayramı: 4 days

		return $holidays;
	}

	/**
	 * Convert a Hijri date to a Gregorian date using an astronomical approximation.
	 *
	 * @param int $gregorianYear
	 * @param int $hijriMonth
	 * @param int $hijriDay
	 * @return Carbon
	 */
	private static function hijriToGregorian(int $gregorianYear, int $hijriMonth, int $hijriDay): Carbon
	{
		// Approximate difference between Gregorian and Hijri years
		$hijriYear = $gregorianYear - 622 + intdiv(($gregorianYear - 622), 33);

		// Convert Hijri date to Julian Day Number
		$julianDay = intdiv(11 * $hijriYear + 3, 30)
			+ 354 * $hijriYear
			+ 30 * ($hijriMonth - 1)
			+ $hijriDay
			+ 1948440 - 385;

		// Convert Julian Day Number to Gregorian date
		$timestamp = jdtounix($julianDay);
		return Carbon::createFromTimestamp($timestamp);
	}

	/**
	 * Check if today is a weekend or a public holiday in Turkey.
	 *
	 * @return bool
	 */
	public static function isDayOff(): bool
	{
		$today = Carbon::today();

		// Check if it's a weekend
		if ($today->isWeekend()) {
			return true;
		}

		// Check if it's a public holiday
		$holidays = self::getPublicHolidays();
		return in_array($today->toDateString(), $holidays, true);
	}
}
