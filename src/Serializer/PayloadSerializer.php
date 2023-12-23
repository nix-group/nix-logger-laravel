<?php

namespace NixLogger\Serializer;

use NixLogger\Item;
use NixLogger\Utils\Helper;

final class PayloadSerializer implements PayloadSerializerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function serialize(Item $item): string
    {
        $payload = [
            'ts' => gmdate('Y-m-d\TH:i:s\Z'),
        ];

        return sprintf("%s", Helper::encode($payload));
    }
}
