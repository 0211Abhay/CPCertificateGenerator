<?php
require 'vendor/autoload.php'; // for PHPSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['excelFile']['tmp_name'], $_POST['eventName'], $_POST['eventDate'])) {
    $file = $_FILES['excelFile']['tmp_name'];
    $eventName = $_POST['eventName'];
    $eventDate = date('d/m/Y', strtotime($_POST['eventDate']));
 // Format the date

    $spreadsheet = IOFactory::load($file);
    $worksheet = $spreadsheet->getActiveSheet();
    
    // Path to the certificate template (JPG)
    $templatePath = 'certificate.jpg';

    // Ensure output directory exists
    if (!is_dir('./Certificates')) {
        mkdir('./Certificates', 0777, true);
    }

    foreach ($worksheet->getRowIterator() as $rowIndex => $row) {
        if ($rowIndex === 0) {
            continue; // Skip header row
        }

        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        $data = [];
        foreach ($cellIterator as $cell) {
            $data[] = $cell->getValue();
        }

        $name = $data[0]; // Name
        $semester = $data[1]; // Semester

        generateCertificate($name, $semester, $eventName, $eventDate, $templatePath);
    }
}

function generateCertificate($name, $semester, $eventName, $eventDate, $templatePath) {
    $image = imagecreatefromjpeg($templatePath);
    if ($image === false) {
        die("Error loading image template.");
    }

    $textColor = imagecolorallocate($image, 5, 55, 155);

    $fontPath = './TIMES.TTF'; // Path to your .ttf font file
    $fontSizeName = 75;
    $fontSizeSemester = 40;
    $fontSizeEvent = 40;
    $fontSizeDate = 30;

    $imageWidth = imagesx($image);

    // Name
    $nameY = 775;
    $nameBoundingBox = imagettfbbox($fontSizeName, 0, $fontPath, $name);
    $nameWidth = $nameBoundingBox[2] - $nameBoundingBox[0];
    $nameX = ($imageWidth - $nameWidth) / 2;
    imagettftext($image, $fontSizeName, 0, $nameX, $nameY, $textColor, $fontPath, $name);

    // Semester
    $semesterY = 850;
    $semesterBoundingBox = imagettfbbox($fontSizeSemester, 0, $fontPath, $semester);
    $semesterWidth = $semesterBoundingBox[2] - $semesterBoundingBox[0];
    $semesterX = ($imageWidth - $semesterWidth) / 2 - 30;
    imagettftext($image, $fontSizeSemester, 0, $semesterX, $semesterY, $textColor, $fontPath, $semester);

    // Event Name
    $eventY = 913; // Adjust as needed
    $eventBoundingBox = imagettfbbox($fontSizeEvent, 0, $fontPath, $eventName);
    $eventWidth = $eventBoundingBox[2] - $eventBoundingBox[0];
    $eventX = ($imageWidth - $eventWidth) / 2;
    imagettftext($image, $fontSizeEvent, 0, $eventX, $eventY, $textColor, $fontPath, $eventName);

    // Event Date
    $dateY = 1230; // Adjust as needed
    $dateBoundingBox = imagettfbbox($fontSizeDate, 0, $fontPath, $eventDate);
    $dateWidth = $dateBoundingBox[2] - $dateBoundingBox[0];
    $dateX = (($imageWidth - $dateWidth) / 2)+25;
    imagettftext($image, $fontSizeDate, 0, $dateX, $dateY, $textColor, $fontPath, $eventDate);

    $outputFilePath = "./Certificates/{$name}_certificate.jpg";
    imagejpeg($image, $outputFilePath);

    imagedestroy($image);
}


?>
