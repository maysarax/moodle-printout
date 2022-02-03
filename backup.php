<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * printout question exporter.
 *
 * @package    qformat_printout
 * @copyright  2018 Stefan Weber (webers@technikum-wien.at)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */




defined('MOODLE_INTERNAL') || die();

require('writetohtml.php');
/**
 * printout question exporter.
 *
 * Exports questions in a printer and human-friendly format.
 *
 * @copyright  2018 Stefan Weber (webers@technikum-wien.at)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 class qformat_printout extends qformat_default {

    public function provide_export() {
        return true;
    }

    protected function writetitle($questiontext) {
        return "<p><li class=\"questiontext\">" . strip_tags($questiontext) . "</li></p>";
      }

    // Turns question into printable format.
    protected function writequestion($question) {

        global $OUTPUT;

        // Category name
        if ($question->qtype=='category') {
            $categoryname = str_replace('$course$/top/',"",$question->category);
            return "</ul> <span class=\"category\"> {$categoryname} </span> <ul class='questionlist'>";
        }

        // Print questions depends on question type.
        $expout .= "<div>";
        switch($question->qtype) {
            case 'multichoice':
                $expout .= $this->writetitle($question->questiontext);
                $expout .= "<ul class=\"multichoice\">\n";
                foreach ($question->options->answers as $answer) {
                    //remove <p> tags from answers since the default <p> tags mess up formatting for multiple choice answers
                    $answertext = strip_tags($answer->answer);
                    $answerpoints = $answer->fraction * 100 . "%";
                    if ($answerpoints > 0) {
                      $class = '"correct points"';
                    } else {
                      $class = '"wrong points"';
                    }
                    $expout .= " <li><span class=$class>($answerpoints)  </span><span class=\"mcanswer\">{$answertext} </span></li>";
                }
                $expout .= "</ul>\n";
                break;
            case 'calculatedmulti':
            case 'multichoiceset':
            case 'oumultiresponse':
            case 'truefalse':
                $expout .= $this->writetitle($question->questiontext);
                $expout .= "<ul class=\"multichoice\">\n";
                foreach ($question->options->answers as $answer) {
                    if ($answer->fraction > 0) {
                      $class = 'correct';
                    } else {
                      $class = 'wrong';
                    }
                    $answertext = strip_tags($answer->answer);
                    $expout .= " <li class=\"mcanswer $class\">{$answertext}</li>";
                }
                $expout .= "</ul>\n";
                break;
            case 'match':
                $expout .= $this->writetitle($question->questiontext);
                $expout .= "<ul>";
                foreach ($question->options->subquestions as $subquestion) {
                    $questiontext = $subquestion->questiontext;
                    $answertext = $subquestion->answertext;
                    $answertext = strip_tags($answertext);
                    if ($questiontext) {
                        $expout .= "<li>$questiontext-> <span class=\"correct\">$answertext</span></li>";
                    } else {
                        $expout .= "<li><p>XXX</p>-> <span class=\"wrong\">$answertext</span></li>";
                    }
                }
                $expout .= "</ul>";
                break;
            case 'description':
                $expout .= $this->writetitle($question->questiontext);
                break;
            case 'gapfill':
            case 'select':
                $expout .= $this->writetitle($question->questiontext);
                foreach ($question->options->answers as $answer) {
                    $expout .= "{$answer->answer}, ";
                }
                break;
            case 'ddwtos':
            case 'gapselect':
                $i = 1;
                $questiontext = strip_tags($question->questiontext);
                foreach ($question->options->answers as $answer) {
                  $questiontext = str_replace('[[' . $i . ']]', '<span class="correct">[[' . $answer->answer . ']]</span>', $questiontext);
                  $i++;
                }
                $expout .= "<p><li class=\"questiontext\"> {$questiontext}</li></p>";
                foreach ($question->options->answers as $answer) {
                  $expout .= "{$answer->answer}, ";
                }
                break;
            case 'multianswer':
                $expout .= $this->writetitle($question->questiontext);
                $expout .= "<ul>";
                foreach ($question->options->questions as $subquestion) {
                  $expout .= "<li class=\"mcanswer\">{$subquestion->questiontext}</li>";
                }
                $expout .= "</ul>";
                break;
            case 'ddimageortext':
            case 'ddmarker':
                $expout .= $this->writetitle($question->questiontext);
                $expout .= get_string('notsupported', 'qformat_printout');
                break;
            default:
                $expout .= $this->writetitle($question->questiontext);
                if (count($question->options->answers) > 1) {
                  $expout .= "<ul>";
                  foreach ($question->options->answers as $answer) {
                      $expout .= "<li>". strip_tags($answer->answer) ."</li>";
                    }
                  $expout .= "</ul>";
                } else {
                  foreach ($question->options->answers as $answer) {
                    $expout .= "<p>" . get_string('answer') . ": " . strip_tags($answer->answer) . "</p>";
                  }
                }
            $expout .= "<br>";
        }

        // Feedback
        if ($question->generalfeedback) {
          $expout .= get_string('feedback', 'question') . ": ";
          $expout .= $question->generalfeedback;
        }

        // Question type
        $expout .= "<p class=\"questiontype\">{$question->name} ";
        $expout .= " (" . get_string('pluginname', "qtype_{$question->qtype}");
        if ($question->options->single) {
          $expout .= " / " . get_string('answersingleyes', 'qtype_multichoice');
        }
        $expout .= ")</p>";

        // End of question
        $expout .= "<hr></div>";
        return $expout;
    }


    protected function presave_process($content) {
        // Override method to allow us to add printout headers and footers.

        global $CFG;

        // Include CSS
        //$csslines = file( "{$CFG->dirroot}/question/format/printout/printout.css" );
        //$css = implode( ' ', $csslines );

        $xp =  "<!DOCTYPE html PUBLIC \"-//W3C//DTD printout 1.0 Strict//EN\"\n";
        $xp .= "  \"http://www.w3.org/TR/printout1/DTD/printout1-strict.dtd\">\n";
        $xp .= "<html xmlns=\"http://www.w3.org/1999/printout\">\n";
        $xp .= "<head>\n";
        $xp .= "<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\" />\n";
        $xp .= "<style type=\"text/css\">\n";
        //$xp .= $css;
        $xp .= "</style>\n";
        $xp .= "</head>\n";
        $xp .= "<body>\n";
        $xp .= $content;
        $xp .= "</body>\n";
        $xp .= "</html>\n";

        //return $xp;


     

    $pdf=new PDF_HTML();
    $pdf->SetFont('Arial','',12);
    $pdf->AddPage();
    $pdf->WriteHTML($xp);

    $file = time().'.pdf';
    $pdf->output($file,'D');

    }

    public function export_file_extension() {
        //return '.html';
    }
}



-------------------------------------------------------------

<?php
//HTML2PDF by Clément Lavoillotte
//ac.lavoillotte@noos.fr
//webmaster@streetpc.tk
//http://www.streetpc.tk

require('fpdf.php');

//function hex2dec
//returns an associative array (keys: R,G,B) from
//a hex html code (e.g. #3FE5AA)
function hex2dec($couleur = "#000000"){
    $R = substr($couleur, 1, 2);
    $rouge = hexdec($R);
    $V = substr($couleur, 3, 2);
    $vert = hexdec($V);
    $B = substr($couleur, 5, 2);
    $bleu = hexdec($B);
    $tbl_couleur = array();
    $tbl_couleur['R']=$rouge;
    $tbl_couleur['V']=$vert;
    $tbl_couleur['B']=$bleu;
    return $tbl_couleur;
}

//conversion pixel -> millimeter at 72 dpi
function px2mm($px){
    return $px*25.4/72;
}

function txtentities($html){
    $trans = get_html_translation_table(HTML_ENTITIES);
    $trans = array_flip($trans);
    return strtr($html, $trans);
}
////////////////////////////////////

class PDF_HTML extends FPDF
{
//variables of html parser
protected $B;
protected $I;
protected $U;
protected $HREF;
protected $fontList;
protected $issetfont;
protected $issetcolor;

function __construct($orientation='P', $unit='mm', $format='A4')
{
    //Call parent constructor
    parent::__construct($orientation,$unit,$format);
    //Initialization
    $this->B=0;
    $this->I=0;
    $this->U=0;
    $this->HREF='';
    $this->fontlist=array('arial', 'times', 'courier', 'helvetica', 'symbol');
    $this->issetfont=false;
    $this->issetcolor=false;
}

function WriteHTML($html)
{
    //HTML parser
    $html=strip_tags($html,"<b><u><i><a><img><p><br><strong><em><font><tr><blockquote>"); //supprime tous les tags sauf ceux reconnus
    $html=str_replace("\n",' ',$html); //remplace retour à la ligne par un espace
    $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE); //éclate la chaîne avec les balises
    foreach($a as $i=>$e)
    {
        if($i%2==0)
        {
            //Text
            if($this->HREF)
                $this->PutLink($this->HREF,$e);
            else
                $this->Write(5,stripslashes(txtentities($e)));
        }
        else
        {
            //Tag
            if($e[0]=='/')
                $this->CloseTag(strtoupper(substr($e,1)));
            else
            {
                //Extract attributes
                $a2=explode(' ',$e);
                $tag=strtoupper(array_shift($a2));
                $attr=array();
                foreach($a2 as $v)
                {
                    if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
                        $attr[strtoupper($a3[1])]=$a3[2];
                }
                $this->OpenTag($tag,$attr);
            }
        }
    }
}

function OpenTag($tag, $attr)
{
    //Opening tag
    switch($tag){
        case 'STRONG':
            $this->SetStyle('B',true);
            break;
        case 'EM':
            $this->SetStyle('I',true);
            break;
        case 'B':
        case 'I':
        case 'U':
            $this->SetStyle($tag,true);
            break;
        case 'A':
            $this->HREF=$attr['HREF'];
            break;
        case 'IMG':
            if(isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT']))) {
                if(!isset($attr['WIDTH']))
                    $attr['WIDTH'] = 0;
                if(!isset($attr['HEIGHT']))
                    $attr['HEIGHT'] = 0;
                $this->Image($attr['SRC'], $this->GetX(), $this->GetY(), px2mm($attr['WIDTH']), px2mm($attr['HEIGHT']));
            }
            break;
        case 'TR':
        case 'BLOCKQUOTE':
        case 'BR':
            $this->Ln(5);
            break;
        case 'P':
            $this->Ln(10);
            break;
        case 'FONT':
            if (isset($attr['COLOR']) && $attr['COLOR']!='') {
                $coul=hex2dec($attr['COLOR']);
                $this->SetTextColor($coul['R'],$coul['V'],$coul['B']);
                $this->issetcolor=true;
            }
            if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist)) {
                $this->SetFont(strtolower($attr['FACE']));
                $this->issetfont=true;
            }
            break;
    }
}

function CloseTag($tag)
{
    //Closing tag
    if($tag=='STRONG')
        $tag='B';
    if($tag=='EM')
        $tag='I';
    if($tag=='B' || $tag=='I' || $tag=='U')
        $this->SetStyle($tag,false);
    if($tag=='A')
        $this->HREF='';
    if($tag=='FONT'){
        if ($this->issetcolor==true) {
            $this->SetTextColor(0);
        }
        if ($this->issetfont) {
            $this->SetFont('arial');
            $this->issetfont=false;
        }
    }
}

function SetStyle($tag, $enable)
{
    //Modify style and select corresponding font
    $this->$tag+=($enable ? 1 : -1);
    $style='';
    foreach(array('B','I','U') as $s)
    {
        if($this->$s>0)
            $style.=$s;
    }
    $this->SetFont('',$style);
}

function PutLink($URL, $txt)
{
    //Put a hyperlink
    $this->SetTextColor(0,0,255);
    $this->SetStyle('U',true);
    $this->Write(5,$txt,$URL);
    $this->SetStyle('U',false);
    $this->SetTextColor(0);
}

}//end of class
?>