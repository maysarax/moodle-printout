
<?php
require('writehtml.php');

$pdf=new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial');
$pdf->WriteHTML
(
'You can<br>
<p align="center">center a line</p>
and add a horizontal rule:<br><hr>'


);
$pdf->Output();
?>