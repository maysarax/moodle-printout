<?php
    
    require('fpdf.php');

    function createPDF($content)
    {
        if (!isset($GLOBALS["pdf_font"])) 
        {
            $GLOBALS["pdf_font"] = 'Arial';
            $GLOBALS["pdf_fontsize"] = 12;
            $GLOBALS["pdf_fontstyle"]='normal';
        }
        echo '$content';
        //require('htmloutput.php');
    
        $pdf=new PDF_HTML();
    
        $pdf->AliasNbPages();
        $pdf->AddPage();
    
        //$pdf->SetFont($GLOBALS["pdf_font"], $GLOBALS["pdf_fontstyle"], $GLOBALS["pdf_fontsize"]);
    
        //$pdf->Write((int) $GLOBALS["pdf_fontsize"] / 2, $content);
        //$htmloutput='';
        
        //$pdf-> WriteHTML  ("<br><br>$htmloutput");
        $fname = tempnam($GLOBALS["tmpdir"], "pdf");
        $pdf->Output($fname, false);
     
        return $fname;


	

}
?>