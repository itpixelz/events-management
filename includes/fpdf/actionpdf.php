<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

require('orientation.php');

class PDF extends PDF_Rotate
{

function RotatedText($x,$y,$txt,$angle)
{
    //Text rotated around its origin
    $this->Rotate($angle,$x,$y);
    $this->Text($x,$y,$txt);
    $this->Rotate(0);
}

function RotatedImage($file,$x,$y,$w,$h,$angle)
{
    //Image rotated around its upper-left corner
    $this->Rotate($angle,$x,$y);
    $this->Image($file,$x,$y,$w,$h);
    $this->Rotate(0);
}
function drawTextBox($strText, $w, $h, $align='L', $valign='T', $border=true)
{
    $xi=$this->GetX();
    $yi=$this->GetY();
    
    $hrow=$this->FontSize;
    $textrows=$this->drawRows($w,$hrow,$strText,0,$align,0,0,0);
    $maxrows=floor($h/$this->FontSize);
    $rows=min($textrows,$maxrows);

    $dy=0;
    if (strtoupper($valign)=='M')
        $dy=($h-$rows*$this->FontSize)/2;
    if (strtoupper($valign)=='B')
        $dy=$h-$rows*$this->FontSize;

    $this->SetY($yi+$dy);
    $this->SetX($xi);

    $this->drawRows($w,$hrow,$strText,0,$align,false,$rows,1);

    if ($border)
        $this->Rect($xi,$yi,$w,$h);
}

function drawRows($w, $h, $txt, $border=0, $align='J', $fill=false, $maxline=0, $prn=0)
{
    $cw=&$this->CurrentFont['cw'];
    if($w==0)
        $w=$this->w-$this->rMargin-$this->x;
    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    $s=str_replace("\r",'',$txt);
    $nb=strlen($s);
    if($nb>0 && $s[$nb-1]=="\n")
        $nb--;
    $b=0;
    if($border)
    {
        if($border==1)
        {
            $border='LTRB';
            $b='LRT';
            $b2='LR';
        }
        else
        {
            $b2='';
            if(is_int(strpos($border,'L')))
                $b2.='L';
            if(is_int(strpos($border,'R')))
                $b2.='R';
            $b=is_int(strpos($border,'T')) ? $b2.'T' : $b2;
        }
    }
    $sep=-1;
    $i=0;
    $j=0;
    $l=0;
    $ns=0;
    $nl=1;
    while($i<$nb)
    {
        //Get next character
        $c=$s[$i];
        if($c=="\n")
        {
            //Explicit line break
            if($this->ws>0)
            {
                $this->ws=0;
                if ($prn==1) $this->_out('0 Tw');
            }
            if ($prn==1) {
                $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
            }
            $i++;
            $sep=-1;
            $j=$i;
            $l=0;
            $ns=0;
            $nl++;
            if($border && $nl==2)
                $b=$b2;
            if ( $maxline && $nl > $maxline )
                return substr($s,$i);
            continue;
        }
        if($c==' ')
        {
            $sep=$i;
            $ls=$l;
            $ns++;
        }
        $l+=$cw[$c];
        if($l>$wmax)
        {
            //Automatic line break
            if($sep==-1)
            {
                if($i==$j)
                    $i++;
                if($this->ws>0)
                {
                    $this->ws=0;
                    if ($prn==1) $this->_out('0 Tw');
                }
                if ($prn==1) {
                    $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
                }
            }
            else
            {
                if($align=='J')
                {
                    $this->ws=($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
                    if ($prn==1) $this->_out(sprintf('%.3F Tw',$this->ws*$this->k));
                }
                if ($prn==1){
                    $this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
                }
                $i=$sep+1;
            }
            $sep=-1;
            $j=$i;
            $l=0;
            $ns=0;
            $nl++;
            if($border && $nl==2)
                $b=$b2;
            if ( $maxline && $nl > $maxline )
                return substr($s,$i);
        }
        else
            $i++;
    }
    //Last chunk
    if($this->ws>0)
    {
        $this->ws=0;
        if ($prn==1) $this->_out('0 Tw');
    }
    if($border && is_int(strpos($border,'B')))
        $b.='B';
    if ($prn==1) {
        $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
    }
    $this->x=$this->lMargin;
    return $nl;
}
}
$event_name =  "The Book Of Moran"; //  event name
$ticket_price =  500 ." /Rs";
$start_time = "10:00 PM";
$end_time = "12:00 PM";
$date = "2017-04-03";
$ticket_id = '111';
$booking_id = '200';
$attendee = 'Aqeel'; // booking person name
$website = "www.google.com";
$venue = "Islamabad 9th evenue - Mareed camp";
$html = '
							
                            
							
                            
                           
                            '.$date.'  '.$start_time.' - '.$end_time.'
                            '.$venue.'
                            Ticket #:'.$ticket_id.'  -  Booking #: '.$booking_id.'
							
                            '.$website
							;
$image = "aqeel.png";
$pdf=new PDF();
$pdf->AddPage();
//$pdf->SetTextColor(100, 20, 100);
$pdf->Image($image,157,20,30,0,'','http://www.fpdf.org');
$pdf->SetFont('Arial','',20);
$pdf->RotatedText(40,60,'PLUGUN',90);
$pdf->SetFillColor('RED');
$pdf->SetXY(20,16);
$pdf->SetFont('Arial','',12);
$pdf->drawTextBox($html, 172, 60, 'L', 'T');
$pdf->SetFont('Arial','',20);
$pdf->RotatedText(54,30,$event_name,0);
$pdf->SetFont('Arial','B',12	);
$pdf->RotatedText(54,38,$attendee,0);
/*
  $pdf->setSourceFile('example.pdf');
  $tplIdx = $pdf->importPage(1);
  $pdf -> useTemplate($tplIdx, 0, 0, 0, 0, false);
*/
$pdf->Output();
?>