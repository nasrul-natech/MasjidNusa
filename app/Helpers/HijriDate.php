<?php

namespace App\Helpers;

/**
 * Gregorian → Hijri (Islamic) calendar converter.
 *
 * Uses PHP IntlCalendar (Umm al-Qura) when the intl extension is loaded,
 * otherwise falls back to the Kuwaiti/tabular algorithm which is accurate
 * to ±1 day for modern dates.
 */
class HijriDate
{
    private const MONTH_NAMES = [
        1  => 'Muharram',
        2  => 'Safar',
        3  => 'Rabiul Awal',
        4  => 'Rabiul Akhir',
        5  => 'Jumadil Awal',
        6  => 'Jumadil Akhir',
        7  => 'Rajab',
        8  => 'Syakban',
        9  => 'Ramadhan',
        10 => 'Syawwal',
        11 => 'Dzulqaidah',
        12 => 'Dzulhijjah',
    ];

    /**
     * Convert a Gregorian date to Hijri.
     *
     * @return array{day: int, month: int, year: int, month_name: string}
     */
    public static function fromGregorian(int $year, int $month, int $day): array
    {
        if (extension_loaded('intl')) {
            return self::viaIntl($year, $month, $day);
        }

        return self::viaAlgorithm($year, $month, $day);
    }

    /**
     * Shorthand: convert a Carbon/DateTime date string like "2026-04-08".
     *
     * @return array{day: int, month: int, year: int, month_name: string}
     */
    public static function fromDateString(string $date): array
    {
        [$y, $m, $d] = array_map('intval', explode('-', $date));

        return self::fromGregorian($y, $m, $d);
    }

    /**
     * Formatted string: "10 Ramadhan 1447 H"
     */
    public static function format(int $year, int $month, int $day): string
    {
        $h = self::fromGregorian($year, $month, $day);

        return "{$h['day']} {$h['month_name']} {$h['year']} H";
    }

    /**
     * Short format: "10 Ramadhan"
     */
    public static function formatShort(int $year, int $month, int $day): string
    {
        $h = self::fromGregorian($year, $month, $day);

        return "{$h['day']} {$h['month_name']}";
    }

    /**
     * Just the Hijri day number.
     */
    public static function dayOnly(int $year, int $month, int $day): int
    {
        return self::fromGregorian($year, $month, $day)['day'];
    }

    public static function getMonthName(int $month): string
    {
        return self::MONTH_NAMES[$month] ?? '';
    }

    // ─── Conversion engines ──────────────────────────────────────

    private static function viaIntl(int $year, int $month, int $day): array
    {
        $ts  = mktime(12, 0, 0, $month, $day, $year);
        $cal = \IntlCalendar::createInstance(null, '@calendar=islamic-umalqura');
        $cal->setTime($ts * 1000);

        $hm = $cal->get(\IntlCalendar::FIELD_MONTH) + 1; // 0-indexed

        return [
            'day'        => $cal->get(\IntlCalendar::FIELD_DAY_OF_MONTH),
            'month'      => $hm,
            'year'       => $cal->get(\IntlCalendar::FIELD_YEAR),
            'month_name' => self::MONTH_NAMES[$hm] ?? '',
        ];
    }

    /**
     * Kuwaiti / tabular algorithm (accurate ±1 day).
     * Adapted from the widely-used algorithm by Robert Harry van Gent.
     */
    private static function viaAlgorithm(int $year, int $month, int $day): array
    {
        $jd = gregoriantojd($month, $day, $year);

        $l = $jd - 1948440 + 10632;
        $n = (int) (($l - 1) / 10631);
        $l = $l - 10631 * $n + 354;

        $j = ((int) ((10985 - $l) / 5316)) * ((int) ((50 * $l) / 17719))
            + ((int) ($l / 5670)) * ((int) ((43 * $l) / 15238));

        $l = $l
            - ((int) ((30 - $j) / 15)) * ((int) ((17719 * $j) / 50))
            - ((int) ($j / 16)) * ((int) ((15238 * $j) / 43))
            + 29;

        $hm = (int) ((24 * $l) / 709);
        $hd = $l - (int) ((709 * $hm) / 24);
        $hy = 30 * $n + $j - 30;

        return [
            'day'        => $hd,
            'month'      => $hm,
            'year'       => $hy,
            'month_name' => self::MONTH_NAMES[$hm] ?? '',
        ];
    }
}
