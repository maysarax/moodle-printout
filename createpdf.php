<?php
require('fpdf.php');


class PDF extends FPDF {

	// Page header
	function Header() {
	// Set font family to Arial bold
		$this->SetFont('Arial','B',20);
		
		// Move to the right
		$this->Cell(80);
		
		// Header
		$this->Cell(50,10,'Heading',1,0,'C');
		
		// Line break
		$this->Ln(20);
	}
    	// Page footer
	function Footer() {
		
		// Position at 1.5 cm from bottom
		$this->SetY(-15);
		
		// Arial italic 8
		$this->SetFont('Arial','I',8);
		
		// Page number
		$this->Cell(0,10,'Page ' .
			$this->PageNo() . '/{nb}',0,0,'C');
	}
}

$pdf=new FPDF();

$pdf->AliasNbPages();
 
//add page automatically for its true parameter
 
//$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

//set font style
//$pdf->WriteHTML('<para><h1>Code</h1><br>');
 // 
$pdf->SetFont('Arial','B',14);

//set the form of pdf
 
//$pdf->SetFont('Arial','B',8);

$htmloutput='';

//Write HTML to pdf file and output that file on the web browser.

//$pdf->WriteHTML2("<br><br>$htmloutput");
$pdf->SetFont('Arial','B',6);

$file = time().'.pdf';

$pdf->output($file,'D');



?>