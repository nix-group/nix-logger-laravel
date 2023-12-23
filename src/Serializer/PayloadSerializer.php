<?php

namespace NixLogger\Serializer;

use NixLogger\Entities\Item;
use NixLogger\Utils\Helper;

final class PayloadSerializer implements PayloadSerializerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function serialize(Item $item): string
    {
        $payload = [
            'timestamp' => $item->getTimestamp() ?? gmdate('Y-m-d\TH:i:s\Z'),
            'level' => $item->getlevel(),
            'context' => $item->getContext(),
            'rootPath' => $item->getRootPath(),
            'environment' => $item->getEnvironment(),
            'timeZone' => $item->getTimeZone(),
            'request' => $item->getRequest(),
            'deviceData' => $item->getDeviceData(),
            'data' => $item->getData(),
            'extraData' => $item->getExtraData(),
        ];

        return sprintf('%s', Helper::encode($payload));
    }
}
