<?php
require_once 'vendor/autoload.php';
require_once 'includes/config.php';
include 'phpqrcode/qrlib.php'; 

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

header('Content-Type: image/png');

$data = isset($_GET['data']) ? urldecode($_GET['data']) : '';

// Create QR code for v4
$qrCode = new QrCode($data);
$qrCode->setSize(300);
$qrCode->setMargin(10);
$qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());

$writer = new PngWriter();
$result = $writer->write($qrCode);

echo $result->getString();
QRcode::png($data); 
?>