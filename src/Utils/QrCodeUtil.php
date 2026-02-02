<?php

namespace Rotaz\FilamentAccounts\Utils;

use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;

class QrCodeUtil
{
    public static function generateQrCodeImage($data)
    {
        if( empty($data) ) return false;
        $renderer = new GDLibRenderer(300);
        $writer = new Writer($renderer);
        return  base64_encode($writer->writeString($data));
    }

}
