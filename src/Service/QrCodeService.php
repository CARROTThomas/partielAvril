<?php

namespace App\Service;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeService
{
    public function createQrCode($name)
    {
        $writer = new PngWriter();
        $qrCode = QrCode::create($name)
            ->setEncoding(new Encoding('UTF-8'))
            ->setSize(120)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        $qrCodes = $writer->write(
            $qrCode
        )->getDataUri();

        return $qrCodes;
    }
}