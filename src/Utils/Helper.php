<?php

namespace NixLogger\Utils;

class Helper
{
    public static function encode($data): string
    {
        $encodedData = json_encode($data);

        return $encodedData;
    }

    public static function decode($data)
    {
        $decodedData = json_decode($data, true);

        return $decodedData;
    }
}
