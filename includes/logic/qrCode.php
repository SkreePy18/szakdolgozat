<?php
    require "../vendor/autoload.php";
    use Endroid\QrCode\QrCode;
    use Endroid\QrCode\Writer\PngWriter;

    function generateQRCode($token) {
        $qr = QrCode::create("https://localhost/szakdolgozat/tokens/redeemToken.php?token=" . $token);
        $writer = new PngWriter();
        $result = $writer->write($qr);

        return $result;
    }

    // echo "<img src='{$result->getDataUri()}'/>";

?>