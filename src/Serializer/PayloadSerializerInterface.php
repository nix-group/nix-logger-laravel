<?php

namespace NixLogger\Serializer;

use NixLogger\Entities\Item;

interface PayloadSerializerInterface
{
    /**
     * Serializes the given event object into a string.
     */
    public static function serialize(Item $event): string;
}
