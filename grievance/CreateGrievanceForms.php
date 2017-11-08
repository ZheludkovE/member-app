<?php

require('fpdf/fpdf.php');


class VariableStream
{
    private $varname;
    private $position;

    function stream_open($path, $mode, $options, &$opened_path)
    {
        $url = parse_url($path);
        $this->varname = $url['host'];
        if(!isset($GLOBALS[$this->varname]))
        {
            trigger_error('Global variable '.$this->varname.' does not exist', E_USER_WARNING);
            return false;
        }
        $this->position = 0;
        return true;
    }

    function stream_read($count)
    {
        $ret = substr($GLOBALS[$this->varname], $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    function stream_eof()
    {
        return $this->position >= strlen($GLOBALS[$this->varname]);
    }

    function stream_tell()
    {
        return $this->position;
    }

    function stream_seek($offset, $whence)
    {
        if($whence==SEEK_SET)
        {
            $this->position = $offset;
            return true;
        }
        return false;
    }
    
    function stream_stat()
    {
        return array();
    }
}


class IRPDF extends FPDF {

    protected $FontSpacingPt;      // current font spacing in points
    protected $FontSpacing;

//    function Header() {
//        // Logo
//        $this->Image('images/logo.png', 15, 12, 49);
//        // Arial bold 12
//        $this->SetFont('Arial', 'B', 12);
//        $this->SetFontSpacing(1);
//  
//        $this->Cell(0, 15, "", 0, 1);
//        $this->Cell(0, 1, 'Utility Workers Union of America', 0, 1, 'C');
//        $this->Cell(0, 10, 'Local 1-2, AFL-CIO', 0, 1, 'C');
//        $this->Cell(0, 13, 'INFORMATION REQUEST FORM', 0, 1, 'C');
//        $this->Cell(0, 10, "", 0, 1);
//        $this->SetFontSpacing(0);
//    }

    function SetFontSpacing($size) {
        if ($this->FontSpacingPt == $size)
            return;
        $this->FontSpacingPt = $size;
        $this->FontSpacing = $size / $this->k;
        if ($this->page > 0) {
            $this->_out(sprintf('BT %.3f Tc ET', $size));
        }
    }

    protected function _dounderline($x, $y, $txt) {
        // Underline text
        $up = $this->CurrentFont['up'];
        $ut = $this->CurrentFont['ut'];
        $w = $this->GetStringWidth($txt) + $this->ws * substr_count($txt, ' ') + (strlen($txt) - 1) * $this->FontSpacing;
        return sprintf('%.2F %.2F %.2F %.2F re f', $x * $this->k, ($this->h - ($y - $up / 1000 * $this->FontSize)) * $this->k, $w * $this->k, -$ut / 1000 * $this->FontSizePt);
    }
    
     function __construct($orientation='P', $unit='mm', $format='A4')
    {
        parent::__construct($orientation, $unit, $format);
        // Register var stream protocol
        stream_wrapper_register('var', 'VariableStream');
    }

    function MemImage($data, $x=null, $y=null, $w=0, $h=0, $link='')
    {
        // Display the image contained in $data
        $v = 'img'.md5($data);
        $GLOBALS[$v] = $data;
        $a = getimagesize('var://'.$v);
        if(!$a)
            $this->Error('Invalid image data');
        $type = substr(strstr($a['mime'],'/'),1);
        $this->Image('var://'.$v, $x, $y, $w, $h, $type, $link);
        unset($GLOBALS[$v]);
    }

    function GDImage($im, $x=null, $y=null, $w=0, $h=0, $link='')
    {
        // Display the GD image associated with $im
        ob_start();
        imagepng($im);
        $data = ob_get_clean();
        $this->MemImage($data, $x, $y, $w, $h, $link);
    }


}

class GFPDF extends FPDF {

    protected $FontSpacingPt;      // current font spacing in points
    protected $FontSpacing;


    function SetFontSpacing($size) {
        if ($this->FontSpacingPt == $size)
            return;
        $this->FontSpacingPt = $size;
        $this->FontSpacing = $size / $this->k;
        if ($this->page > 0) {
            $this->_out(sprintf('BT %.3f Tc ET', $size));
        }
    }

    protected function _dounderline($x, $y, $txt) {
        // Underline text
        $up = $this->CurrentFont['up'];
        $ut = $this->CurrentFont['ut'];
        $w = $this->GetStringWidth($txt) + $this->ws * substr_count($txt, ' ') + (strlen($txt) - 1) * $this->FontSpacing;
        return sprintf('%.2F %.2F %.2F %.2F re f', $x * $this->k, ($this->h - ($y - $up / 1000 * $this->FontSize)) * $this->k, $w * $this->k, -$ut / 1000 * $this->FontSizePt);
    }
    
    function __construct($orientation='P', $unit='mm', $format='A4')
    {
        parent::__construct($orientation, $unit, $format);
        // Register var stream protocol
        stream_wrapper_register('var', 'VariableStream');
    }

    function MemImage($data, $x=null, $y=null, $w=0, $h=0, $link='')
    {
        // Display the image contained in $data
        $v = 'img'.md5($data);
        $GLOBALS[$v] = $data;
        $a = getimagesize('var://'.$v);
        if(!$a)
            $this->Error('Invalid image data');
        $type = substr(strstr($a['mime'],'/'),1);
        $this->Image('var://'.$v, $x, $y, $w, $h, $type, $link);
        unset($GLOBALS[$v]);
    }

    function GDImage($im, $x=null, $y=null, $w=0, $h=0, $link='')
    {
        // Display the GD image associated with $im
        ob_start();
        imagepng($im);
        $data = ob_get_clean();
        $this->MemImage($data, $x, $y, $w, $h, $link);
    }

}


class CreateInfoRequestForm {
    
    protected $mDesigneeId;
    protected $mDesigneeName;
    protected $mDate;
    protected $mToName;
    protected $mReDirecction;
    protected $mSignatureFile;   
    protected $mCompanyName;
    protected $mOtherText;
    protected $mInfoByDate;
    
    public function __construct($fromDate, $toName, $designeeName,  $designeeId, $reDirecction, $infoByDate, $signatureFile, $otherText) {
        $this->mDesigneeName = $designeeName;
        $this->mDesigneeId = $designeeId;
        $this->mDate = $fromDate;
        $this->mToName = $toName;
        $this->mReDirecction = $reDirecction;
        $this->mSignatureFile = $signatureFile;
        $this->mOtherText = $otherText;
        $this->mInfoByDate = $infoByDate;
    }
    
    public function createForm($infoRequestList) {
   
        
   
        $id01=false;$id02=false;$id03=false;$id04=false;$id05=false;$id06=false;
        $id07=false;$id08=false;$id09=false;$id10=false;$id11=false;$id12=false;
        $id13=false;$id14=false;$id15=false;$id16=false;$id17=false;$id18=false;
        $id19=false;$id20=false;$id21=false;$id22=false;$id23=false;$id24=false;
        $id25=false;$id26=false;$id27=false;$id28=false;$id29=false;$id30=false;
        $id31=false;$id32=false;$id33=false;$id34=false;$id35=false;
        
        foreach ($infoRequestList as $infoRequest) {
                switch ($infoRequest->getId()) {    
                        case 1:
                            $id01=true;
                            break;
                        case 2:
                            $id02=true;
                            break;
                        case 3:
                            $id03=true;
                            break;
                        case 4:
                            $id04=true;
                            break;
                        case 5:
                            $id05=true;
                            break;
                        case 6:
                            $id06=true;
                            break;
                        case 7:
                            $id07=true;
                            break;
                        case 8:
                            $id08=true;
                            break;
                        case 9:
                            $id09=true;
                            break;
                        case 10:
                            $id10=true;
                            break;
                        case 11:
                            $id11=true;
                            break;
                        case 12:
                            $id12=true;
                            break;
                        case 13:
                            $id13=true;
                            break;
                        case 14:
                            $id14=true;
                            break;
                        case 15:
                            $id15=true;
                            break;
                        case 16:
                            $id16=true;
                            break;
                        case 17:
                            $id17=true;
                            break;
                        case 18:
                            $id18=true;
                            break;
                        case 19:
                            $id19=true;
                            break;
                        case 20:
                            $id20=true;
                            break;
                        case 21:
                            $id21=true;
                            break;
                        case 22:
                            $id22=true;
                            break;
                        case 23:
                            $id23=true;
                            break;
                        case 24:
                            $id24=true;
                            break;
                        case 25:
                            $id25=true;
                            break;
                        case 26:
                            $id26=true;
                            break;
                        case 27:
                            $id27=true;
                            break;
                        case 28:
                            $id28=true;
                            break;
                        case 29:
                            $id29=true;
                            break;
                        case 30:
                            $id30=true;
                            break;
                        case 31:
                            $id31=true;
                            break;
                        case 32:
                            $id32=true;
                            break;
                        case 33:
                            $id33=true;
                            break;
                        case 34:
                            $id34=true;
                            break;
                        case 35:
                            $id35=true;
                            break;
                        default:
                        break;
                    }
        }
        
        
        $pdf = new IRPDF();
      
        $pdf->AliasNbPages();
        
        $pdf->SetAutoPageBreak(false);

        $pdf->AddPage();
        
        $pdf->Image('images/logo.png', 15, 12, 49);

        $pdf->SetFont('Arial', 'B', 12);
           
        $pdf->SetFontSpacing(1);

        $pdf->Cell(0, 15, "", 0, 1);
        $pdf->Cell(0, 1, 'Utility Workers Union of America', 0, 1, 'C');
        $pdf->Cell(0, 10, 'Local 1-2, AFL-CIO', 0, 1, 'C');
        $pdf->Cell(0, 13, 'INFORMATION REQUEST FORM', 0, 1, 'C');
        $pdf->Cell(0, 10, "", 0, 1);
        $pdf->SetFontSpacing(0);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 2,'',0,1);
        $pdf->Cell(22);
        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->SetXY($x + 10, $y-1);
        $pdf->Cell(80, 8, "$this->mToName", 0, 1);
      
        
        $pdf->SetXY($x , $y);
        $pdf->Cell(80, 8, "To: _____________________", 0, 1);
        $pdf->SetXY($x + 85, $y);
        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->SetXY($x + 14, $y-1);
        $pdf->Cell(80, 8, "$this->mDesigneeName", 0, 1);
        $pdf->SetXY($x , $y);
        
        
        $pdf->Cell(0, 8, 'From:  ____________________'   , 0, 1);
        $pdf->Cell(22);
        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->SetXY($x + 12, $y-1);
        $pdf->Cell(80, 8, $this->mDate, 0, 1);
        $pdf->SetXY($x , $y);  
        $pdf->Cell(80, 8, 'Date:  ___________________', 0, 1);
        $pdf->SetXY($x + 85, $y);
        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->SetXY($x + 9, $y-1);
        $pdf->Cell(80, 8, $this->mReDirecction, 0, 1);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 8, 'Re: _______________________', 0, 1);
        $pdf->Cell(22);
        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->Cell(0, 8, "Dear:   $this->mToName,", 0, 1);
        $pdf->SetXY($x + 85, $y);
        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->SetXY($x + 9, $y-1);
        $pdf->Cell(80, 8, $this->mDesigneeId, 0, 1);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 8, 'ID# _______________________', 0, 1);
        $pdf->Cell(34);
        $pdf->Cell(0, 8, 'The Union hereby requests the following information to:', 0, 1);
        $pdf->Cell(0, 4,'',0,1);
        $checkbox ='';
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
         
        if($id01==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        
        
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Monitor compliance with the contract', 0, 1);

        if($id02==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Investigate whether a grievance exists', 0, 1);
        
         if($id03==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Prepare for a grievance meeting', 0, 1);
        
         if($id04==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'BU', 11);
        $pdf->Cell(0, 4, 'Prepare for arbitration', 0, 1);
        $pdf->SetXY($x+47, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, '(DELIVER TO VICE PRESIDENT LOCAL 1-2 ONLY)', 0, 1);
        
         if($id05==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Decide whether to drop a grievance or move it through the steps', 0, 1);
  
        $pdf->Cell(23);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
       
        
        $pdf->SetFont('Times', '', 11);
        $pdf->SetXY($x+90, $y-1);
        $pdf->Cell(0, 15, $this->mInfoByDate, 0, 1);
        
        $pdf->SetFont('Times', 'B', 11);
        $pdf->SetXY($x+11, $y);
        $pdf->Cell(0, 15, 'Please provide the following information by     _____________', 0, 1);
        
        if($id06==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 3,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Accident reports', 0, 1);
        
         if($id20==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->SetXY($x+82, $y);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $pdf->SetXY($x+89, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Customer complaints', 0, 1);
        
        if($id07==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Attendance records', 0, 1);
        
         if($id21==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->SetXY($x+82, $y);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $pdf->SetXY($x+89, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Customer lists', 0, 1);
        
        if($id08==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Company Manuals', 0, 1);
        
         if($id22==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->SetXY($x+82, $y);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $pdf->SetXY($x+89, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Disciplinary records', 0, 1);
        
        if($id09==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Supervisors guide to discipline', 0, 1);
        
         if($id23==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->SetXY($x+82, $y);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $pdf->SetXY($x+89, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Equipment specifications', 0, 1);
        
        if($id10==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Company memos', 0, 1);
        
         if($id24==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->SetXY($x+82, $y);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $pdf->SetXY($x+89, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Evaluations', 0, 1);
        
        if($id11==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->MultiCell(65, 5, 'Contracts with customers, suppliers and subcontractors', 0, 1);
        
         if($id25==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->SetXY($x+82, $y);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $pdf->SetXY($x+89, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Inspection records', 0, 1);
        
         if($id26==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->SetXY($x+82, $y+5);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $pdf->SetXY($x+89, $y+5);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Interview notes', 0, 1);
        
        if($id12==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Correspondence', 0, 1);
        
         if($id27==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->SetXY($x+82, $y);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $pdf->SetXY($x+89, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Investigative reports', 0, 1);
        
        if($id13==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Photographs', 0, 1);
        
         if($id28==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->SetXY($x+82, $y);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $pdf->SetXY($x+89, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Job assignment records', 0, 1);
        
        if($id14==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Reports and studies', 0, 1);
        
         if($id29==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->SetXY($x+82, $y);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $pdf->SetXY($x+89, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Job descriptions', 0, 1);
        
        if($id15==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Security guard records', 0, 1);
        
         if($id30==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->SetXY($x+82, $y);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $pdf->SetXY($x+89, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Material safety data sheets', 0, 1);      
        
        if($id16==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Security reports', 0, 1);
  
        if($id31==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->SetXY($x+82, $y);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $pdf->SetXY($x+89, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Payroll records', 0, 1);   
        
        if($id17==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Seniority lists', 0, 1);
        
        if($id32==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->SetXY($x+82, $y);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $pdf->SetXY($x+89, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Personnel files', 0, 1); 
   
         if($id18==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Training manuals', 0, 1);
        
        if($id33==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        
        $pdf->SetXY($x+82, $y);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $pdf->SetXY($x+89, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Supervisors notes', 0, 1); 

         if($id19==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Wage and Salary records', 0, 1);
        
         if($id34==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->SetXY($x+82, $y);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $pdf->SetXY($x+89, $y);
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(0, 4, 'Work rules', 0, 1);         
        
         if($id35==true)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+7, $y);
        $pdf->SetFont('Times', 'BU', 11);
        $pdf->MultiCell(140, 5, 'Any and all other documents on which the Company relied in taking the action or which it contends supports the action which is the subject of the grievance.', 0, 1);
        
        $otherLen =0;
        
        if(!empty($this->mOtherText))
        {
            $checkbox='3';
            $otherLen= strlen($this->mOtherText);
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(23);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetFont('Times', '', 11);
        $pdf->SetXY($x+20, $y-1);
        
        if ($otherLen>0 && $otherLen<70)
        {
            $pdf->Cell(0, 4, $this->mOtherText, 0, 1);
        }
        else if (!empty($this->mOtherText)) {
             $pdf->Cell(0, 4, 'Read the back of page.', 0, 1);
        }
        
        $pdf->SetFont('Times', 'B', 11);
        $pdf->SetXY($x+7, $y);
   
        $pdf->Cell(0, 4, 'Other _______________________________________________________________', 0, 1);
        
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(45);
        $pdf->Cell(0, 4, '(Use back of page if more room is necessary)', 0, 1);
        
        $pdf->Cell(0, 6,'',0,1);
        $pdf->Cell(22);
        $pdf->Cell(0, 4, 'Sincerely,', 0, 1);
        
        $pdf->Cell(0, 9,'',0,1);
        $pdf->Cell(22);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Cell(0, 4, '______________________________________', 0, 1);
        if (!empty($this->mSignatureFile))
        {  
             $pdf->MemImage($this->mSignatureFile, $x+4, $y-7, 30);  
        }
        
        $pdf->Cell(0, 1,'',0,1);
        $pdf->Cell(22);
        $pdf->Cell(0, 4, 'Business Agent, Shop Steward', 0, 1);
        
        if (!empty($this->mOtherText) && $otherLen>=70)
        {
            $pdf->AddPage();
            $pdf->Cell(15);
            $pdf->SetFont('Times', '', 11);
            $pdf->MultiCell(140, 8, $this->mOtherText ,0,1);
            
        }
        
        return $pdf->Output("", "S");
        
      
    }

}


class CreateGeneralForm {
    
   
    protected $mGrievance;
    protected $mDate;
    protected $mDBQuery;
    
    
    public function __construct($formDate, $grievance, $dbQuery) {
        $this->mGrievance = $grievance;
        $this->mDate = $formDate;
        $this->mDBQuery = $dbQuery;
    }
    
    public function createForm() {
        

        
        $pdf = new GFPDF('P','mm','Letter');;

        $pdf->AddPage();
        $pdf->SetAutoPageBreak(false);
        $pdf->Image('images/logo_bw.png', 27, 26, 37);
        // Arial bold 12
        $pdf->SetFont('Times', 'B', 20);
   
        $pdf->Cell(0, 8, "", 0, 1);
        $pdf->Cell(0, 8, 'Utility Workers Union of America, AFL-CIO', 0, 1, 'C');
        $pdf->Cell(0, 10, 'Local 1-2', 0, 1, 'C');
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetFont('Times', '', 15);
       
        
        $pdf->Cell(0, 5, '5 West 37   Street New York, NY 10018', 0, 1, 'C');
        $pdf->SetXY($x+75, $y);
        $pdf->SetFont('Times', '', 12);
        $pdf->Cell(0, 5, 'th', 0, 1);
        $pdf->SetFont('Times', '', 14);
        $pdf->Cell(0, 5, '(212) 575-4400', 0, 1, 'C');
        $pdf->Cell(0, 5, "", 0, 1);
        $pdf->SetFont('Times', 'B', 16);
        $pdf->Cell(0, 5, 'GRIEVANCE REPORT', 0, 1, 'C');
        $pdf->Cell(0, 23, " ", 0, 1);
     

        
        $pdf->SetFont('Times','', 12);
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+32, $y-1);
        $pdf->Cell(0, 7, $this->mGrievance->getMember()->getFirstName(). " ".$this->mGrievance->getMember()->getLastName() ,0,1);
        $pdf->SetXY($x, $y);
        $pdf->SetXY($x+145, $y-1);
        $pdf->Cell(0, 7, $this->mGrievance->getMember()->getEmpNumber() ,0,1);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 7, "Member's Name ____________________________________            Employee No.  _________", 0, 1 );
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+30, $y-1);
        $pdf->Cell(0, 7, $this->mGrievance->getAddress1(),0,1);
        $pdf->SetXY($x, $y);
        $pdf->SetXY($x+138, $y-1);
        $pdf->Cell(0, 7, $this->mGrievance->getPhone() ,0,1);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 7, "Home Address _____________________________________   Home Phone No. _______________", 0, 1 );
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+5, $y-1);
        $pdf->Cell(0, 7, $this->mGrievance->getAddress2() ,0,1);
        $pdf->SetXY($x+119, $y-1);
        $pdf->Cell(0, 7, $this->mGrievance->getTitle() ,0,1);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 7, "__________________________________________________  Title ___________________________", 0, 1 );
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+22, $y-1);
        $pdf->Cell(0, 7, $this->mGrievance->getMember()->getCompanyPrefix() ,0,1);
        $pdf->SetXY($x+74, $y-1);
        $pdf->Cell(0, 7, $this->mGrievance->getDepartment() ,0,1);
        $pdf->SetXY($x+119, $y-1);
        $pdf->Cell(0, 7, $this->mGrievance->getBureau() ,0,1);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 7, "Company ________________ Department ______________ Bureau ___________________________", 0, 1 );
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+28, $y-1);
        $pdf->Cell(0, 7, $this->mGrievance->getWorkLocation(),0,1);
        $pdf->SetXY($x+100, $y-1);
        $pdf->Cell(0, 7, $this->mGrievance->getSupervisor() ,0,1);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 7, "Work Location _______________________   Supervisor ____________________________________", 0, 1 );
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Cell(9);
        $pdf->Cell(0, 3, "Nature of Grievance     PLEASE CHECK THE APPROPRIATE BOX BELOW", 0, 1 );   
        $pdf->Line($x+45, $y+2, $x+49, $y+2);
        $pdf->Cell(0, 6, "", 0, 1);
      

        
        $pdf->Cell(18);
        $checkbox=''; 
        if(!empty($this->mGrievance->getGrievanceNature()) && $this->mGrievance->getGrievanceNature()->getId()==1)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+2, $y);
        $pdf->SetFont('Times', '', 12);
        $pdf->Cell(0, 4, 'Termination', 0, 1);
        
        
        if(!empty($this->mGrievance->getGrievanceNature()) && $this->mGrievance->getGrievanceNature()->getId()==2)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 4 , "", 0, 1);
        $pdf->Cell(18);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+2, $y);
        $pdf->SetFont('Times', '', 12);
        $pdf->Cell(0, 4, 'Suspension', 0, 1);
        
        if(!empty($this->mGrievance->getGrievanceNature()) && $this->mGrievance->getGrievanceNature()->getId()==3)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 4 , "", 0, 1);
        $pdf->Cell(18);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+2, $y);
        $pdf->SetFont('Times', '', 12);
        $pdf->Cell(0, 4, 'Denied Progression', 0, 1);
        
        if(!empty($this->mGrievance->getGrievanceNature()) && $this->mGrievance->getGrievanceNature()->getId()==4)
        {
            $checkbox='3';
        }
        else
        {
           $checkbox =''; 
        }
        $pdf->Cell(0, 4 , "", 0, 1);
        $pdf->Cell(18);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkbox, 1, 0);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+2, $y);
        $pdf->SetFont('Times', '', 12);
        $pdf->Cell(0, 4, 'Denied Merit', 0, 1);
        

       
        $pdf->Cell(0, 1 , "", 0, 1);
        $pdf->Cell(24);
        $pdf->Cell(0, 7, "Other (Warnings) Give a brief explanation below", 0, 1 );
        
        $pdf->Cell(0, 1 , "", 0, 1);
        $pdf->Cell(24);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+1, $y-1);
        $pdf->MultiCell(160, 7, $this->mGrievance->getNatureOfGrivanceOther() ,0,1);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 7, "_________________________________________________________________________", 0, 1 );
        $pdf->Cell(24);
        $pdf->Cell(0, 7, "_________________________________________________________________________", 0, 1 );
        $pdf->Cell(24);
        $pdf->Cell(0, 7, "_________________________________________________________________________", 0, 1 );
        $pdf->Cell(24);
        $pdf->Cell(0, 7, "_________________________________________________________________________", 0, 1 );
        
        $pdf->Cell(9);
        $pdf->Cell(0, 3, "Clause of Contract Violated (list articles violated)", 0, 1 ); 
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+25, $y);
        $pdf->MultiCell(160, 7, $this->mGrievance->getClauseOfContractViolated() ,0,1);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 23, " ", 0, 1);
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Cell(0, 7, "Remedy",0,1);
        
    
        
        if(!empty($this->mGrievance->getGrievanceRemedy()) && $this->mGrievance->getGrievanceRemedy()->getId()==1)
        {
            $pdf->SetFont('ZapfDingbats','', 10);
            $pdf->SetXY($x+18, $y+1);
            $pdf->Cell(4, 4, '8', 0, 1);
        }
        $pdf->SetFont('Times', '', 12);
        $pdf->SetXY($x+23, $y);
        $pdf->Cell(0, 7, "To be made whole, including but not limited to any lost wages, benefits, merit",0,1);
        $pdf->Cell(37);
        $pdf->Cell(0, 7, "increases and progressions.", 0, 1 );
        $pdf->Cell(37);
     
     
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        if(!empty($this->mGrievance->getRemedyOther()))
        {
            $pdf->SetFont('ZapfDingbats','', 10);
            $pdf->SetXY($x-10, $y+1);
            $pdf->Cell(4, 4, "8", 0, 1);
        }
        $pdf->SetXY($x, $y);
        $pdf->SetFont('Times', '', 12);
        $pdf->Cell(0, 7, "Other (Give a brief explanation below)", 0, 1 );
       
        $pdf->Cell(24);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+1, $y-1);
        $pdf->MultiCell(140, 7, $this->mGrievance->getRemedyOther(),0,1);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 7, "_____________________________________________________________________", 0, 1 );
        $pdf->Cell(24);
        $pdf->Cell(0, 7, "_____________________________________________________________________", 0, 1 );
        $pdf->Cell(24);
        $pdf->Cell(0, 7, "_____________________________________________________________________", 0, 1 );
       
        $pdf->Cell(0, 5, " ", 0, 1);
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x+26, $y-1);
        $pdf->Cell(0, 7, $this->mDate,0,1);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 7, "Today's Date ___________________       Signature of Member _________________________", 0, 1 );
        if (!empty($this->mGrievance->getSignatureFile()))
        {  
             $pdf->MemImage($this->mGrievance->getSignatureFile(), $x+112, $y-6, 30);
              
        }
       
        
        $pdf->AddPage();
        $step1 = $this->mGrievance->getSteps()[0];
        $pdf->SetFont('Times', 'BU', 14);
   
       
        
        $pdf->Cell(0, 3, "", 0, 1);
        $pdf->Cell(9);
       
        $pdf->Cell(0, 8, 'Result of 1st Step in Grievance Procedure', 0, 1);
        $pdf->Cell(0, 17, " ", 0, 1);
        $pdf->SetFont('Times', '', 12);
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        if(!empty($step1))
        {  
           if ($step1->getDateOfGrievanceMeeting()!=0)
           {
               $pdf->SetXY($x+50, $y-1);
               $pdf->Cell(0, 9, date("d M, Y", strtotime($step1->getDateOfGrievanceMeeting())) , 0, 1);
           }
                   
        }
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 9, "Date of Grievance Meeting ____________________________________________________________", 0, 1 );
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        if(!empty($step1))
        {  
           $pdf->SetXY($x+40, $y-1);
           $pdf->Cell(0, 9, $step1->getNameOfDesignee() , 0, 1);                   
        }
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 9, "Names of Stewards  __________________________________________________________________", 0, 1 );
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        if(!empty($step1))
        {  
           $pdf->SetXY($x+63, $y-1);
           $pdf->Cell(0, 9, $step1->getCompanyRep() , 0, 1);                   
        }
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 9, "Name of Company Representative  ______________________________________________________", 0, 1 );
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        if(!empty($step1))
        {  
           $pdf->SetXY($x+45, $y-1);
           $pdf->MultiCell(125, 9, $step1->getOtherPresent() , 0, 1);                   
        }
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 9, "Names of Others Present ______________________________________________________________", 0, 1 );
        $pdf->Cell(9);
        $pdf->Cell(0, 9, " __________________________________________________________________________________", 0, 1 );
        $pdf->Cell(0, 5, "", 0, 1);
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        
        $pdf->SetFont('ZapfDingbats','', 10);
        $checkboxYes="";
        $checkboxNo="";
        if(!empty($step1))
        {  
            if(!empty($step1->getInfoRequestedInWritingByDesignee()))
            {
                $checkboxYes="3";
            }
            else
            {
                $checkboxNo="3";
            }
                
        }
        $pdf->SetXY($x, $y+1);
        $pdf->Cell(110);
        $pdf->Cell(4, 4, $checkboxYes, 1, 0);
        $pdf->SetXY($x, $y+1);
        $pdf->Cell(145);
        $pdf->Cell(4, 4, $checkboxNo, 1, 0);
        $pdf->SetFont('Times', '', 12);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 10, "Was information requested in writing by Steward?                              Yes                           No", 0, 1 );
        
 
        
        $pdf->Cell(9);
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $checkboxYes="";
            $checkboxNo="";
            if(!empty($step1))
            {  
                if(!empty($step1->getInfoProvidedByCompany()))
                {
                    $checkboxYes="3";
                }
                else
                {
                    $checkboxNo="3";
                }

            }

            $pdf->SetXY($x, $y+1);
            $pdf->Cell(81);
            $pdf->SetFont('ZapfDingbats','', 10);
            $pdf->Cell(4, 4, $checkboxYes, 1, 0);
            $pdf->SetXY($x, $y+1);
            $pdf->Cell(118);
            $pdf->Cell(4, 4, $checkboxNo, 1, 0);
            $pdf->SetFont('Times', '', 12);
            $pdf->SetXY($x, $y);
        $pdf->Cell(0, 10, "Was information provided by Company Rep?          Yes                             No", 0, 1 );
        
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        
        $checkboxYes="";
        $checkboxNo="";
        if(!empty($step1))
        {  
            if(!empty($step1->getInfoRequestedInWritingByDesignee()))
            {
                $checkboxYes="3";
            }
            else
            {
                $checkboxNo="3";
            }
                
        }
        
        

        $pdf->SetXY($x, $y+1);
        $pdf->Cell(81);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkboxYes, 1, 0);
        $pdf->SetXY($x, $y+1);
        $pdf->Cell(118);
        $pdf->Cell(4, 4, $checkboxNo, 1, 0);
        $pdf->SetFont('Times', '', 12);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 10, "Did Company Rep provide written response?           Yes                             No", 0, 1 );
        $pdf->Cell(9);
        $pdf->Cell(0, 10, "Signature of Steward  ________________________________________", 0, 1 );


    
        $pdf->SetFont('Times', 'BU', 14);
  
        $step2 =null;
        if (count($this->mGrievance->getSteps())>1)
        {
        $step2 = $this->mGrievance->getSteps()[1];
        }
        $pdf->Cell(0, 5, "", 0, 1);
        $pdf->Cell(9);
        $pdf->Cell(0, 8, 'Result of 2nd Step in Grievance Procedure', 0, 1);
        $pdf->Cell(0, 15, " ", 0, 1);
        $pdf->SetFont('Times', '', 12);
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
    
       
        
        if(!empty($step2))
        {  
           if ($step2->getDateOfGrievanceMeeting()!=0)
           {
               $pdf->SetXY($x+50, $y-1);
               $pdf->Cell(0, 8, date("d M, Y", strtotime($step2->getDateOfGrievanceMeeting())) , 0, 1);
           }    
    
        }
       
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 8, "Date of Grievance Meeting ____________________________________________________________", 0, 1 );
        $pdf->Cell(9);
         $x = $pdf->GetX();
        $y = $pdf->GetY();
        if(!empty($step2))
        {  
           $pdf->SetXY($x+50, $y-1);
           $pdf->Cell(0, 9, $step2->getNameOfDesignee() , 0, 1);                   
        }
         
        
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 8, "Name of Agent / Designee  ____________________________________________________________", 0, 1 );
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        if(!empty($step2))
        {  
           $pdf->SetXY($x+63, $y-1);
           $pdf->MultiCell(120, 8, $step2->getCompanyRep() , 0, 1);                   
        }
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 8, "Name of Company Representative  ______________________________________________________", 0, 1 );
        $pdf->Cell(9);
        $pdf->Cell(0, 8, " __________________________________________________________________________________", 0, 1 );
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        if(!empty($step2))
        {  
           $pdf->SetXY($x+45, $y-1);
           $pdf->MultiCell(125, 8, $step2->getOtherPresent() , 0, 1);                   
        }
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 8, "Names of Others Present ______________________________________________________________", 0, 1 );
        $pdf->Cell(9);
        $pdf->Cell(0, 8, " __________________________________________________________________________________", 0, 1 );
        $pdf->Cell(0, 5, "", 0, 1);
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
               
        
        $pdf->SetFont('ZapfDingbats','', 10);
        $checkboxYes="";
        $checkboxNo="";
        if(!empty($step2))
        {  
            if(!empty($step2->getInfoRequestedInWritingByDesignee()))
            {
                $checkboxYes="3";
            }
            else
            {
                $checkboxNo="3";
            }
                
        }
        $pdf->SetXY($x, $y+1);
        $pdf->Cell(110);
        $pdf->Cell(4, 4, $checkboxYes, 1, 0);
        $pdf->SetXY($x, $y+1);
        $pdf->Cell(145);
        $pdf->Cell(4, 4, $checkboxNo, 1, 0);
        $pdf->SetFont('Times', '', 12);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 9, "Was information requested in writing by SBA / Designee?                  Yes                           No", 0, 1 );
        
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $checkboxYes="";
        $checkboxNo="";
        if(!empty($step2))
        {  
            if(!empty($step2->getInfoProvidedByCompany()))
            {
                $checkboxYes="3";
            }
            else
            {
                $checkboxNo="3";
            }
                
        }
        
        $pdf->SetXY($x, $y+1);
        $pdf->Cell(81);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkboxYes, 1, 0);
        $pdf->SetXY($x, $y+1);
        $pdf->Cell(118);
        $pdf->Cell(4, 4, $checkboxNo, 1, 0);
        $pdf->SetFont('Times', '', 12);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 9, "Was information provided by Company Rep?          Yes                             No", 0, 1 );
        
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        
        $checkboxYes="";
        $checkboxNo="";
        if(!empty($step2))
        {  
            if(!empty($step2->getInfoRequestedInWritingByDesignee()))
            {
                $checkboxYes="3";
            }
            else
            {
                $checkboxNo="3";
            }
                
        }
        
        $pdf->SetXY($x, $y+1);
        $pdf->Cell(81);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkboxYes, 1, 0);
        $pdf->SetXY($x, $y+1);
        $pdf->Cell(118);
        $pdf->Cell(4, 4, $checkboxNo, 1, 0);
        $pdf->SetFont('Times', '', 12);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 9, "Did Company Rep provide written response?           Yes                             No", 0, 1 );
        
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        
        $checkboxYes="";
        $checkboxNo="";
        if(!empty($step2))
        {  
            $closedStep=false;
           
            $stepStatus = $this->mDBQuery->getGrievanceStepStatusByStep($step2->getStep()->getId());
            
            foreach($stepStatus as $ss)
            {
              
                if ($ss->getStatus()->getId() == $step2->getStatus()->getId())
                {
                    if ($ss->isCloseStep())
                    {
                        $closedStep=true;
                    }
                }
            }
            if($closedStep)
            {
                $checkboxYes="3";
            }
            else
            {
                $checkboxNo="3";
            }
                
        }
        $pdf->SetXY($x, $y+1);
        $pdf->Cell(25);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkboxYes, 1, 0);
        $pdf->SetXY($x, $y+1);
        $pdf->Cell(117);
        $pdf->Cell(4, 4, $checkboxNo, 1, 0);
        $pdf->SetFont('Times', '', 12);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 12, "Settlement:             Yes  ____________________________                       No  ____________________", 0, 1 );
        $pdf->Cell(0, 4, "", 0, 1);
        $pdf->Cell(9);
        $pdf->Cell(0, 10, "Signature of Business Agent / Designee  __________________________________________", 0, 1 );
        
        $pdf->AddPage();
        
        $step3 =null;
        if (count($this->mGrievance->getSteps())>2)
        {
            $step3 = $this->mGrievance->getSteps()[2];
        }
        
        $pdf->SetFont('Times', 'BU', 14);
   
        $pdf->Cell(0, 5, "", 0, 1);
        $pdf->Cell(9);
        $pdf->Cell(0, 8, 'Result of 3rd Step in Grievance Procedure', 0, 1);
        $pdf->Cell(0, 5, " ", 0, 1);
        $pdf->SetFont('Times', '', 12);
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        
        if(!empty($step3))
        {  
           if ($step3->getDateOfGrievanceMeeting()!=0)
           {
               $pdf->SetXY($x+50, $y-1);
               $pdf->Cell(0, 10, date("d M, Y", strtotime($step3->getDateOfGrievanceMeeting())) , 0, 1);
           }    
    
        }
        
       
        $pdf->SetXY($x, $y);
        
        $pdf->Cell(0, 10, "Date of Grievance Meeting ____________________________________________________________", 0, 1 );
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        if(!empty($step3))
        {  
           $pdf->SetXY($x+50, $y-1);
           $pdf->Cell(0, 10, $step3->getNameOfDesignee() , 0, 1);                   
        }
         
        
        $pdf->SetXY($x, $y);
        
        $pdf->Cell(0, 10, "Name of SBA / Designee  _____________________________________________________________", 0, 1 );
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        if(!empty($step3))
        {  
           $pdf->SetXY($x+63, $y-1);
           $pdf->Cell(0, 9, $step3->getCompanyRep() , 0, 1);                 
        }
        $pdf->SetXY($x, $y);
        
        $pdf->Cell(0, 10, "Name of Company Representative  ______________________________________________________", 0, 1 );        
        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        if(!empty($step3))
        {  
           $pdf->SetXY($x+45, $y-1);
           $pdf->Cell(0, 9, $step3->getOtherPresent() , 0, 1);                  
        }
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 10, "Names of Others Present ______________________________________________________________", 0, 1 );

        $pdf->Cell(9);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        
        $checkboxYes="";
        $checkboxNo="";
        if(!empty($step3))
        {  
            if(!empty($step3->getInfoRequestedInWritingByDesignee()))
            {
                $checkboxYes="3";
            }
            else
            {
                $checkboxNo="3";
            }
                
        }
        $pdf->SetXY($x, $y+1);
        $pdf->Cell(110);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkboxYes, 1, 0);
        $pdf->SetXY($x, $y+1);
        $pdf->Cell(145);
        $pdf->Cell(4, 4, $checkboxNo, 1, 0);
        $pdf->SetFont('Times', '', 12);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 9, "Was information requested in writing by SBA / Designee?                  Yes                           No", 0, 1 );
        
        $pdf->Cell(9);
        $checkboxYes="";
        $checkboxNo="";
        if(!empty($step3))
        {  
            if(!empty($step3->getInfoProvidedByCompany()))
            {
                $checkboxYes="3";
            }
            else
            {
                $checkboxNo="3";
            }
                
        }
        
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x, $y+1);
        $pdf->Cell(81);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkboxYes, 1, 0);
        $pdf->SetXY($x, $y+1);
        $pdf->Cell(118);
        $pdf->Cell(4, 4, $checkboxNo, 1, 0);
        $pdf->SetFont('Times', '', 12);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 9, "Was information provided by Company Rep?          Yes                             No", 0, 1 );
        
        $pdf->Cell(9);
        $checkboxYes="";
        $checkboxNo="";
        if(!empty($step3))
        {  
            if(!empty($step3->getInfoRequestedInWritingByDesignee()))
            {
                $checkboxYes="3";
            }
            else
            {
                $checkboxNo="3";
            }
                
        }
        
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x, $y+1);
        $pdf->Cell(81);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkboxYes, 1, 0);
        $pdf->SetXY($x, $y+1);
        $pdf->Cell(118);
        $pdf->Cell(4, 4, $checkboxNo, 1, 0);
        $pdf->SetFont('Times', '', 12);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 9, "Did Company Rep provide written response?           Yes                             No", 0, 1 );
        
        $pdf->Cell(9);
        $checkboxYes="";
        $checkboxNo="";
        if(!empty($step3))
        {  
            $closedStep=false;
           
            $stepStatus = $this->mDBQuery->getGrievanceStepStatusByStep($step3->getStep()->getId());
            
            foreach($stepStatus as $ss)
            {
              
                if ($ss->getStatus()->getId() == $step3->getStatus()->getId())
                {
                    if ($ss->isCloseStep())
                    {
                        $closedStep=true;
                    }
                }
            }
            if($closedStep)
            {
                $checkboxYes="3";
            }
            else
            {
                $checkboxNo="3";
            }
                
        }
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x, $y+1);
        $pdf->Cell(25);
        $pdf->SetFont('ZapfDingbats','', 10);
        $pdf->Cell(4, 4, $checkboxYes, 1, 0);
        $pdf->SetXY($x, $y+1);
        $pdf->Cell(118);
        $pdf->Cell(4, 4, $checkboxNo, 1, 0);
        $pdf->SetFont('Times', '', 12);
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 9, "Settlement:             Yes  ____________________________                       No  ____________________", 0, 1 );
        $pdf->Cell(9);
        $pdf->Cell(0, 10, "Signature of Senior Business Agent / Designee   ____________________________________________", 0, 1 );

        $pdf->SetFont('Times', 'B', 12);
        $pdf->Cell(0, 5, "", 0, 1);
        $pdf->Cell(0, 8, '   **********Guidelines for Grievance Process**********', 0, 1, 'C');
        
        $pdf->SetFont('Times', '', 12);
        
        $pdf->Cell(0, 10, "", 0, 1);
        $pdf->Cell(9);
        $pdf->Cell(0, 9, "* This form stays in the possession of the Union at all times", 0, 1 );
        $pdf->Cell(9);
        $pdf->Cell(0, 9, "* All information on this form is to be filled out by the union representative", 0, 1 );
        $pdf->Cell(9);
        $pdf->Cell(0, 9, "* Make a copy of Grievance Report, Information Requests and all notes for your records", 0, 1 );
        
        $pdf->Cell(9);
        $pdf->Cell(0, 9, "For example:   Disciplinary Interview Report", 0, 1 );

        $pdf->Cell(35);
        $pdf->Cell(0, 9, "Statements from witnesses", 0, 1 );
        
        $pdf->Cell(35);
        $pdf->Cell(0, 9, "Statement from Grievant", 0, 1 );
        
        $pdf->Cell(35);
        $pdf->Cell(0, 9, "Steward's notes from interviews", 0, 1 );
        
        $pdf->Cell(35);
        $pdf->Cell(0, 5, "Employee Evaluations", 0, 1 );
        $pdf->Cell(35);
        $pdf->Cell(0, 5, "All other relevant documents", 0, 1 );
        
        $pdf->Cell(0, 10, "", 0, 1);
        $pdf->Cell(9);
        $pdf->Cell(0, 9, "* Place originals in envelope and give to next Union Representative in preparation for next step of grievance", 0, 1 );
        $pdf->Cell(9);
        $pdf->Cell(0, 9, "process", 0, 1 );
        
      
        return $pdf->Output("", "S");
    }

}

