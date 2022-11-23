<?php

class PolylineEncoder {
    // function for convert sequence data eg. 1234;5678;9012;3456;7890 to polylines
    public static function rawDataToPolyline($rawData = '') {
        $data = explode(";",$rawData);
        $polylines = [];

        for ($i = 0; $i < count($data); $i = $i + 2) {
            if (!isset($data[$i + 1])) {
                array_push($polylines, [$data[$i], 0]);
                continue;
            }

            array_push($polylines, [
                $data[$i],
                $data[$i + 1]
            ]);
        }
        return $polylines;
    }

    public static function latLngToFixed($latLng, $factor) {

        $latLngArray = [];
        if ((!is_array($latLng)) && 
            is_object($latLng) && 
            property_exists($latLng, 'lat') &&
            property_exists($latLng, 'lng')) {
            $latLngArray = [
                self::customRound($latLng->lat),
                self::customRound($latLng->lng)
                // $latLng->lat,
                // $latLng->lng
            ];
            return $latLngArray;
        }
        return [
            self::customRound($latLng[0] * $factor),
            self::customRound($latLng[1] * $factor)
        ];
    }
    public static function encode(Array $path, int $precission){
        $factor = pow(10, $precission);

        return self::polylineEncodeLine($path, $factor);
    }
    public static function polylineEncodeLine($path, $factor) {
        $v = [];
        $start = [0, 0];
        $end = [];

        for($i = 0, $I = count($path); $i < $I; ++$i) {
            $end = self::latLngToFixed($path[$i], $factor);
            self::polylineEncodeSigned(self::customRound($end[0]) - self::customRound($start[0]), $v);
            self::polylineEncodeSigned(self::customRound($end[1]) - self::customRound($start[1]), $v);
            $start = $end;
        }

        return join('', $v);
    }
    public static function polylineEncodeSigned($num, &$v){
        return self::polylineEncodeUnsigned($num < 0 ? ~($num << 1) : $num << 1, $v);
    }

    public static function consoleLog($msg) {
		echo '<script type="text/javascript">' .
          'console.log(' . $msg . ');</script>';
	}

    public static function polylineEncodeUnsigned($num, &$v){
        while($num >= 0x20) {
            array_push($v, chr((0x20 | ($num & 0x1f)) + 63));
            $num >>= 5;
        }
        array_push($v, chr($num + 63) );
        return $v;
    }
    public static function customRound($num) {
        return (int) floor(abs($num) + 0.5) * ($num < 0 ? -1 : 1);
    }
}
