<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Code 128 (subset B) barcode encoder that draws an SVG with no external library or internet.
 *
 * Code 128 is what every USB/Bluetooth barcode scanner reads out of the box. Subset B covers
 * printable ASCII (letters, digits, "-", "/", spaces), which fits our barcodes like "BC-GWTS-24-NB".
 * A scanner "types" the decoded text followed by Enter into the POS search box.
 */
class Code128
{
    /** Bar/space widths (in modules) for symbol values 0-106. Value 104 = Start B, 106 = Stop. */
    public const PATTERNS = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112',
    ];

    private const START_B = 104;
    private const STOP = 106;
    private const QUIET_ZONE = 10; // blank modules required on each side for scanners

    /** Symbol values for the text, including start, checksum and stop. */
    public static function encode(string $text): array
    {
        if ($text === '') {
            throw new InvalidArgumentException('Cannot encode an empty barcode.');
        }

        $values = [self::START_B];
        $checksum = self::START_B;

        foreach (str_split($text) as $position => $char) {
            $code = ord($char);
            if ($code < 32 || $code > 127) {
                throw new InvalidArgumentException("Character '{$char}' cannot be printed in a Code 128 barcode.");
            }
            $value = $code - 32;
            $values[] = $value;
            $checksum += $value * ($position + 1);
        }

        $values[] = $checksum % 103;
        $values[] = self::STOP;

        return $values;
    }

    /** Alternating bar/space widths for the whole symbol, starting with a bar. */
    public static function widths(string $text): array
    {
        $widths = [];
        foreach (self::encode($text) as $value) {
            foreach (str_split(self::PATTERNS[$value]) as $w) {
                $widths[] = (int) $w;
            }
        }

        return $widths;
    }

    /**
     * An SVG drawing of the barcode. It scales to the width of its container, so the same SVG fits
     * any label size; bars keep their relative widths, which is all a scanner needs.
     */
    public static function svg(string $text, int $height = 40): string
    {
        $widths = self::widths($text);
        $x = self::QUIET_ZONE;
        $bars = '';

        foreach ($widths as $i => $w) {
            if ($i % 2 === 0) { // even positions are bars, odd positions are spaces
                $bars .= '<rect x="' . $x . '" y="0" width="' . $w . '" height="' . $height . '"/>';
            }
            $x += $w;
        }

        $total = $x + self::QUIET_ZONE;

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $total . ' ' . $height . '" '
            . 'preserveAspectRatio="none" shape-rendering="crispEdges" role="img" '
            . 'aria-label="' . htmlspecialchars($text, ENT_QUOTES) . '">'
            . '<rect width="' . $total . '" height="' . $height . '" fill="#fff"/><g fill="#000">' . $bars . '</g></svg>';
    }

    /** Width of the symbol in modules, including quiet zones (used to warn about tiny labels). */
    public static function moduleCount(string $text): int
    {
        return array_sum(self::widths($text)) + 2 * self::QUIET_ZONE;
    }
}
