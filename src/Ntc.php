<?php
/**
 * User: Leon J
 * Date: 2017/9/15
 * Time: 下午2:27
 */

namespace JYJ1993126\NameThatColor;

class Ntc
{
    /**
     * @var array
     */
    private $names = [];

    /**
     * @var
     */
    private static $instances = [];

    /**
     * @param string $library
     * @param string $dir
     * @return Ntc
     */
    public static function getInstance($library = 'en', $dir = './')
    {
        if (!isset(self::$instances[$library])) {
            $path = "{$dir}lib/$library.txt";
            if (!file_exists($path)) {
                return self::getInstance();
            }
            self::$instances[$library] = new self($path);
        }
        return self::$instances[$library];
    }

    /**
     * Ntc constructor.
     * @param $path
     */
    protected function __construct($path)
    {
        $lib = fopen($path, 'r');
        while (($line = fgets($lib)) !== false) {
            list($hex, $name) = explode(',', trim($line), 2);
            $color = '#' . $hex;
            $this->names[] = array_merge(
                [
                    $hex, $name
                ],
                self::rgb($color),
                self::hsl($color)
            );
        }
    }

    /**
     * @param $color
     * @return array
     */
    public function get($color)
    {
        $color = strtoupper($color);
        $len = strlen($color);
        if ($len < 3 || $len > 7) {
            return ['#000000', 'Invalid Color: ' . $color, false];
        }
        if ($len % 3 === 0) {
            $color = '#' . $color;
        }
        if (strlen($color) === 4) {
            $color = '#' . $color[1] . $color[1] . $color[2] . $color[2] . $color[3] . $color[3];
        }
        list($r, $g, $b) = self::rgb($color);
        list($h, $l, $s) = self::hsl($color);
        $ndf1 = $ndf2 = $ndf = 0;
        $cl = $df = -1;

        foreach ($this->names as $index => $name) {
            if ($color === '#' . $name[0])
                return ['#' . $name[0], $name[1], true];

            $ndf1 = ($r - $name[2]) ** 2 + ($g - $name[3]) ** 2 + ($b - $name[4]) ** 2;
            $ndf2 = ($h - $name[5]) ** 2 + ($s - $name[6]) ** 2 + ($l - $name[7]) ** 2;
            $ndf = $ndf1 + $ndf2 * 2;
            if ($df < 0 || $df > $ndf) {
                $df = $ndf;
                $cl = $index;
            }
        }
        return ($cl < 0 ? ['#000000', 'Invalid Color: ' . $color, false] : ['#' . $this->names[$cl][0], $this->names[$cl][1], false]);
    }

    /**
     * @param $color
     * @return mixed
     */
    public function name($color)
    {
        return $this->get($color)[1];
    }

    /**
     * @param $color
     * @return array
     */
    public static function rgb($color)
    {
        return [hexdec('0x' . substr($color, 1, 2)), hexdec('0x' . substr($color, 3, 2)), hexdec('0x' . substr($color, 5, 2))];
    }

    /**
     * @param $color
     * @return array
     */
    public static function hsl($color)
    {
        list($r, $g, $b) = array_map(function ($val) {
            return $val / 255;
        }, self::rgb($color));

        $min = min($r, $g, $b);
        $max = max($r, $g, $b);
        $delta = $max - $min;
        $l = ($min + $max) / 2;

        $s = 0;
        if ($l > 0 && $l < 1)
            $s = $delta / ($l < 0.5 ? (2 * $l) : (2 - 2 * $l));

        $h = 0;
        if ($delta > 0) {
            if ($max == $r && $max != $g) $h += ($g - $b) / $delta;
            if ($max == $g && $max != $b) $h += (2 + ($b - $r) / $delta);
            if ($max == $b && $max != $r) $h += (4 + ($r - $g) / $delta);
            $h /= 6;
        }
        return [(int)$h * 255, (int)$s * 255, (int)$l * 255];
    }
}
