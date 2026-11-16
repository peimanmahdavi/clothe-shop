<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Lightweight Gregorian → Jalali (Solar Hijri) converter.
 */
class Jalali
{
    public static function format(Carbon|\DateTimeInterface|string|null $date, string $format = 'Y/m/d H:i'): string
    {
        if ($date === null || $date === '') {
            return '—';
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        [$jy, $jm, $jd] = self::toJalali(
            (int) $carbon->format('Y'),
            (int) $carbon->format('n'),
            (int) $carbon->format('j'),
        );

        $map = [
            'Y' => (string) $jy,
            'm' => str_pad((string) $jm, 2, '0', STR_PAD_LEFT),
            'd' => str_pad((string) $jd, 2, '0', STR_PAD_LEFT),
            'H' => $carbon->format('H'),
            'i' => $carbon->format('i'),
            's' => $carbon->format('s'),
        ];

        $out = '';
        $len = strlen($format);
        for ($i = 0; $i < $len; $i++) {
            $char = $format[$i];
            $out .= $map[$char] ?? $char;
        }

        return $out;
    }

    public static function toJalali(int $gy, int $gm, int $gd): array
    {
        $gDaysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $jDaysInMonth = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

        $gy2 = $gm > 2 ? $gy + 1 : $gy;
        $days = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100)
            + intdiv($gy2 + 399, 400) + $gd
            + $gDaysInMonth[$gm - 1];

        $jy = -1595 + (33 * intdiv($days, 12053));
        $days %= 12053;
        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        $jm = 1;
        $jd = 1;
        for ($i = 0; $i < 12; $i++) {
            if ($days <= $jDaysInMonth[$i]) {
                $jm = $i + 1;
                $jd = max($days, 1);
                break;
            }
            $days -= $jDaysInMonth[$i];
        }

        return [$jy, $jm, $jd];
    }

    public static function monthName(int $month): string
    {
        return [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
            5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
            9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
        ][$month] ?? '';
    }
}
