<?php
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->setIsRemoteEnabled(true);
$dompdf = new Dompdf($options);

// Fix Dompdf font path issue
$options->set('defaultFont', 'Helvetica');
$dompdf->setOptions($options);

$html = '
<!DOCTYPE html>
<html>
<head>
<style>
/* font-family: \'Arial\', sans-serif; */
/* Removed external font references */
font-family: Helvetica, sans-serif;
</style>
</head>
<body>
<h1>Report</h1>
<p>This is a sample report generated as PDF.</p>
</body>
</html>
';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("report.pdf");
