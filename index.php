<?php
require('fpdf.php');
ini_set('display_errors', 'On');
error_reporting(E_ALL);


class PDF extends FPDF
{

    function TableOne($header, $data,$x,$long)
    {
        // Colors, line width and bold font
        $this->SetFillColor(255,255,255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(.5);
        $this->SetFont('','B');
        // Header
        $w = $long;
        $this->SetX(57);
        $this->Cell(40,4,utf8_decode('MÓDULO'),0,0,'C');
        $this->SetLineWidth(.5);
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C');
        $this->SetLineWidth(.5);
        $this->Ln();
        // Color and font restoration
        $this->SetFillColor(198,224,180);
        $this->SetTextColor(0);

        $this->SetFont('');
        // Data
        $fill = true;

        foreach($data as $row)
        {
            $this->SetX($x);

            for ($i=0; $i <count($w) ; $i++) {
                $this->Cell($w[$i],6,utf8_decode($row[$i]),'LR',0,'C',true);

            }
            //$this->Cell($w[1],6,utf8_decode($row[1]),'LR',0,'L',$fill);
            //$this->Cell($w[2],6,utf8_decode($row[2]),'LR',0,'R',$fill);
            $this->Ln();
            //$fill = !$fill;
        }
        // Closing line
        $this->SetX($x);
        $this->Cell(array_sum($w),0,'','T');
    }

    function TableTwo($header, $data,$x,$long,$y)
    {
        // Colors, line width and bold font
        $this->SetFillColor(255,255,255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(.5);
        $this->SetFont('','B');
        // Header
        $w = $long;
        $this->Cell(45,4,utf8_decode('MÓDULO'),0,0,'C');
        $this->SetLineWidth(.5);
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C');
        $this->SetLineWidth(.5);
        $this->Ln();
        // Color and font restoration
        $this->SetFillColor(198,224,180);
        $this->SetTextColor(0);

        $this->SetFont('');
        // Data
        $fill = true;

        //foreach($data as $row)
        for ($j=0; $j <count($data) ; $j++)
        {
            $this->SetX($x);

            for ($i=0; $i <count($w) ; $i++) {
                if($i+1 == count($w)){
                    $this->SetFillColor(180,198,231);
                }else{
                    $this->SetFillColor(198,224,180);
                }
                if($j+1 == count($data)){
                    $this->SetFont('','B');
                    $this->Cell($w[$i],6,utf8_decode($data[$j][$i]),'LTR',0,'C',false);
                }else{
                    $this->Cell($w[$i],6,utf8_decode($data[$j][$i]),'LR',0,'C',true);
                }
                $this->SetFont('');

            }

            $this->Ln();

        }

        $this->SetX($x);
        $this->Cell(array_sum($w),0,'','T');
    }

    function TableThree($data,$x,$long,$y)
    {
        // Colors, line width and bold font
        $this->SetFillColor(255,255,255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(.5);
        $this->SetFont('','B',9);
        // Header
        $w = $long;
        $this->SetX($x);
        $this->SetLineWidth(.5);

        $this->Cell(20.5,4,utf8_decode(''),'LTR',0,'C');
        $this->Cell(41,4,utf8_decode('Promedio de participación'),'LTR',0,'C');
        $this->Cell(41,4,utf8_decode('Curso Virtual de '),'LTR',0,'C');
        $this->Cell(41,4,utf8_decode('Examen de Módulo'),'LTR',0,'C');
        $this->Ln();
        $this->SetX($x);
        $this->Cell(20.5,4,utf8_decode(''),'LR',0,'C');
        $this->Cell(41,4,utf8_decode('de módulo'),'LR',0,'C');
        $this->Cell(41,4,utf8_decode('Harvard'),'LR',0,'C');
        $this->Cell(41,4,utf8_decode(''),'LR',0,'C');
        $this->Ln();

        $this->Cell(45,4,utf8_decode('MÓDULO'),0,0,'C');
        $this->Cell(20.5,4,utf8_decode('Nota Final'),'LR',0,'C');
        $this->Cell(41,4,utf8_decode('30%'),1,0,'C');
        $this->Cell(41,4,utf8_decode('40%'),1,0,'C');
        $this->Cell(41,4,utf8_decode('30%'),1,0,'C');
        $this->Ln();


      $this->SetXY($x,175+$y);

        $this->SetTextColor(255);
        $this->MultiCell(20.5,4,utf8_decode('Nota Participante'),'LBR','C');
        $this->SetXY(75.5,175+$y);
        $this->SetTextColor(0);
        $this->MultiCell(20.5,4,utf8_decode('Nota Participante'),1,'C');
        $this->SetXY(96,175+$y);
        $this->MultiCell(20.5,4,utf8_decode('Promedio Grupo'),1,'C');
        $this->SetXY(116.5,175+$y);
        $this->MultiCell(20.5,4,utf8_decode('Nota Participante'),1,'C');
        $this->SetXY(137,175+$y);
        $this->MultiCell(20.5,4,utf8_decode('Promedio Grupo'),1,'C');
        $this->SetXY(157.5,175+$y);
        $this->MultiCell(20.5,4,utf8_decode('Nota Participante'),1,'C');
        $this->SetXY(178,175+$y);
        $this->MultiCell(20.5,4,utf8_decode('Promedio Grupo'),1,'C');

        $this->SetFillColor(198,224,180);
        $this->SetTextColor(0);

        $this->SetFont('Arial','',11);

        for ($j=0; $j <count($data) ; $j++)
        {
            $this->SetX($x);

            $fill = false;
            for ($i=0; $i <count($w) ; $i++) {

                if($fill){
                    $this->SetFillColor(180,198,231);
                }else{
                    $this->SetFillColor(198,224,180);
                }

                $this->Cell($w[$i],6,utf8_decode($data[$j][$i]),'LR',0,'C',true);
                $this->SetFont('');

                if( $i != 0){
                    $fill = !$fill;
                }

            }

            $this->Ln();
        }

        $this->SetX($x);
        $this->Cell(array_sum($w),0,'','T');
    }
/*
    function TableFour($data,$x,$long,$y)
    {

        $this->SetFillColor(255,255,255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(.5);
        $this->SetFont('','B',9);

        // Header
        $w = $long;
        $this->SetX($x);
        $this->SetLineWidth(.5);

        $this->Cell(20.5,4,utf8_decode(''),'LTR',0,'C');
        $this->Cell(61.5,4,utf8_decode('Participación en clase'),1,0,'C');
        $this->Cell(61.5,4,utf8_decode('Examen de comprobación de lectura'),1,0,'C');
        $this->Ln();

        $this->SetX($x);
        $this->Cell(20.5,4,utf8_decode('Nota Final'),'LR',0,'C');
        $this->Cell(61.5,4,utf8_decode('50%'),1,0,'C');
        $this->Cell(61.5,4,utf8_decode('50%'),1,0,'C');


        $this->Ln();
        $this->SetXY($x,211+$y);
        $this->SetTextColor(255);
        $this->MultiCell(20.5,4,utf8_decode('Nota'),'LBR','C');
        $this->SetXY(75.5,211+$y);
        $this->SetTextColor(0);
        $this->MultiCell(30.75,4,utf8_decode('Nota Participante'),1,'C');
        $this->SetXY(106.2,211+$y);
        $this->MultiCell(30.75,4,utf8_decode('Promedio Grupo'),1,'C');
        $this->SetXY(137.05,211+$y);
        $this->MultiCell(30.75,4,utf8_decode('Nota Participante'),1,'C');
        $this->SetXY(167.8,211+$y);
        $this->MultiCell(30.75,4,utf8_decode('Promedio Grupo'),1,'C');
        //$this->Ln();

        $this->SetFillColor(198,224,180);
        $this->SetTextColor(0);

        $this->SetFont('Arial','',11);

        for ($j=0; $j <count($data) ; $j++)
        {
            $this->SetX($x);

            $fill = false;
            for ($i=0; $i <count($w) ; $i++) {

                if($fill){
                    $this->SetFillColor(180,198,231);
                }else{
                    $this->SetFillColor(198,224,180);
                }

                $this->Cell($w[$i],6,utf8_decode($data[$j][$i]),'LR',0,'C',true);
                $this->SetFont('');

                if( $i != 0){
                    $fill = !$fill;
                }

            }
            $this->Ln();
        }
        $this->SetX($x);
        $this->Cell(array_sum($w),0,'','T');
    }*/

    function TableFive($data,$x,$long,$y)
    {

        $this->SetFillColor(255,255,255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(.5);
        $this->SetFont('','B',9);
        // Header
        $w = $long;
        $this->SetX($x);
        $this->SetLineWidth(.5);

        $this->Cell(20.5,4,utf8_decode(''),'LTR',0,'C');
        $this->Cell(41,4,utf8_decode('Participación Casos'),'LTR',0,'C');
        $this->Cell(41,4,utf8_decode('Control de Lectura '),'LTR',0,'C');
        $this->Cell(41,4,utf8_decode('Participación Dasafio'),'LTR',0,'C');
        $this->Cell(41,4,utf8_decode('Trabajo Final'),'LTR',0,'C');
        $this->Ln();
        $this->SetX($x);
        $this->Cell(20.5,4,utf8_decode(''),'LR',0,'C');
        $this->Cell(41,4,utf8_decode('Intercorp'),'LR',0,'C');
        $this->Cell(41,4,utf8_decode(''),'LR',0,'C');
        $this->Cell(41,4,utf8_decode('Estratégico'),'LR',0,'C');
        $this->Cell(41,4,utf8_decode(''),'LR',0,'C');
        $this->Ln();

        $this->SetX($x);
        $this->Cell(20.5,4,utf8_decode('Nota Final'),'LR',0,'C');
        $this->Cell(41,4,utf8_decode('20%'),1,0,'C');
        $this->Cell(41,4,utf8_decode('10%'),1,0,'C');
        $this->Cell(41,4,utf8_decode('20%'),1,0,'C');
        $this->Cell(41,4,utf8_decode('50%'),1,0,'C');
        $this->Ln();

      /*  $this->Ln();*/
        $this->SetXY($x,234.2+$y+7.5);
        $this->SetTextColor(255);
        $this->MultiCell(20.5,8.5,utf8_decode('Nota Final'),'LBR','C');
        $this->SetXY(40.5,242.2+$y);
        $this->SetTextColor(0);
        $this->MultiCell(20.5,4,utf8_decode('Nota Participante'),1,'C');
        $this->SetXY(61,242.2+$y);
        $this->MultiCell(20.5,4,utf8_decode('Promedio Grupo'),1,'C');
        $this->SetXY(81.5,242.2+$y);
        $this->MultiCell(20.5,4,utf8_decode('Nota Participante'),1,'C');
        $this->SetXY(102,242.2+$y);
        $this->MultiCell(20.5,4,utf8_decode('Promedio Grupo'),1,'C');
        $this->SetXY(122.5,242.2+$y);
        $this->MultiCell(20.5,4,utf8_decode('Nota Participante'),1,'C');
        $this->SetXY(143,242.2+$y);
        $this->MultiCell(20.5,4,utf8_decode('Promedio Grupo'),1,'C');
        $this->SetXY(163.5,242.2+$y);
        $this->MultiCell(20.5,4,utf8_decode('Nota Participante'),1,'C');
        $this->SetXY(184,242.2+$y);
        $this->MultiCell(20.5,4,utf8_decode('Promedio Grupo'),1,'C');


        //$this->Ln();

        $this->SetFillColor(198,224,180);
        $this->SetTextColor(0);

        $this->SetFont('Arial','',11);
        for ($j=0; $j <count($data) ; $j++)
        {
            $this->SetX($x);

            $fill = false;
            for ($i=0; $i <count($w) ; $i++) {

                if($fill){
                    $this->SetFillColor(180,198,231);
                }else{
                    $this->SetFillColor(198,224,180);
                }

                $this->Cell($w[$i],6,utf8_decode($data[$j][$i]),'LR',0,'C',true);
                $this->SetFont('');

                if( $i != 0){
                    $fill = !$fill;
                }

            }

            $this->Ln();
        }
        $this->SetX($x);
        $this->Cell(array_sum($w),0,'','T');
    }



    function Header()
    {

        /*$nombre_curso = 'Programa Discovery intermedio Curso (G3)';

        // Logo
        $this->Image('img/logo.png',10,8,50);
        // Arial bold 15
        $this->SetFont('Arial','B',14);
        // Movernos a la derecha
        $this->Cell(80);
        // Título
        $this->Cell(95,10,utf8_decode('Programa'),1,1,'R');
        $this->Cell(175,10,utf8_decode($nombre_curso),1,1,'R');
        // Salto de línea
        $this->Ln(20);*/
    }

    // Pie de página
    function Footer()
    {
       /* // Posición: a 1,5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Número de página
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');*/
    }
}


$nombre_curso = 'Programa Discovery intermedio Curso (G3)';
$reporte_titulo = 'REPORTE PARCIAL DE RESULTADOS';
$reporte_user = 'Diana María Helen Cárdenas Ávila';

$y = 2;
$cuadroUno = 0;
$cuadroDos = 0;
$cuadroTres = 5;
$cuadroCuatro = 8;
$cuadroCinco = 7;

// Creación del objeto de la clase heredada
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

$pdf->SetFillColor(35, 32, 41);
$pdf->Rect(10, 10, 190, 25, 'F');
//$pdf->Line(10, 10, 15, 15);
//$pdf->SetXY(15, 15);
//$pdf->Cell(15, 6, '10, 10', 0 , 1); //Celda
$pdf->Ln(5);
$pdf->Image('img/logo.png',10,10,50);
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(255,255,255);
//$pdf->Cell(80);
$pdf->Cell(189,10,utf8_decode('Programa'),0,1,'R');
$pdf->Cell(189,10,utf8_decode($nombre_curso),0,1,'R');
$pdf->Ln(4);

$pdf->SetTextColor(35, 32, 41);
$pdf->Cell(189,10,utf8_decode($reporte_titulo),0,1,'C');
//$pdf->Ln(4);
$pdf->Rect(13, 50, 34, 34, 'D');
$pdf->Image('img/perfil.jpg',15,52,30);
$pdf->SetFont('Arial','B',11);
$pdf->SetXY(10, 85);
$pdf->MultiCell(40,4,utf8_decode($reporte_user),0,'C',false);


//*********************        ASISTENCIA      ********************************//
$pdf->SetXY(58,50);
$pdf->Cell(40,6,utf8_decode('I. ASISTENCIA'),0,1,'L');

$pdf->SetFillColor(198,224,180);
$pdf->SetFont('Arial','',11);
$pdf->SetXY(58,66);
$pdf->SetLineWidth(.10);
$pdf->Cell(40,6,utf8_decode('Módulo Líder'),'LTR',0,'C',true);
$pdf->SetXY(58,72);
$pdf->Cell(40,6,utf8_decode('Módulo Colaborador'),'LR',0,'C',true);
$pdf->SetXY(58,78);
$pdf->Cell(40,6,utf8_decode('Módulo Negocio'),'LR',0,'C',true);
$pdf->SetXY(58,84);
$pdf->Cell(40,6,utf8_decode('Módulo integrador'),'LBR',0,'C',true);
//$this->SetLineWidth(.5);
$pdf->SetY(59);
$header = array('DURACIÓN (Hrs)', 'ASISTENCIA (Hrs)', 'ASISTENCIA');
$data = array( array(16,16,'100%') , array(20,20,'100%') , array(20,'Pendiente','Pendiente') , array(16,'Pendiente','Pendiente'));
$long = array(35, 38, 29);
$pdf->TableOne($header,$data,97,$long);
//*********************        ASISTENCIA      ********************************//


//*********************        CONSOLIDADO DE NOTAS       ********************************//
$pdf->SetFont('Arial','B',11);
$pdf->SetXY(10,97+$y+$cuadroDos);
$pdf->Cell(40,6,utf8_decode('II. CONSOLIDADO DE NOTAS'),0,1,'L');
//$pdf->SetY(58);
$header = array('Nota final del módulo', 'Promedio del Grupo 3 Discovery *');
$data = array( array(17.00,16.75) , array(17.27,16.53) , array('Pendiente','Pendiente') , array('Pendiente','Pendiente') ,  array('Pendiente','Pendiente'));
$long = array(72, 72);
$pdf->TableTwo($header,$data,55,$long,$cuadroDos);

$pdf->SetFillColor(198,224,180);
$pdf->SetFont('Arial','',11);
$pdf->SetXY(10,110+$y+$cuadroDos);
$pdf->SetLineWidth(.5);
$pdf->Cell(45,6,utf8_decode('Módulo Líder'),'LTR',0,'C',true);
$pdf->SetXY(10,116+$y+$cuadroDos);
$pdf->Cell(45,6,utf8_decode('Módulo Colaborador'),'LR',0,'C',true);
$pdf->SetXY(10,122+$y+$cuadroDos);
$pdf->Cell(45,6,utf8_decode('Módulo Negocio'),'LR',0,'C',true);
$pdf->SetXY(10,128+$y+$cuadroDos);
$pdf->Cell(45,6,utf8_decode('Módulo integrador'),'LR',0,'C',true);
//$pdf->SetXY(10,134+$y+$cuadroDos);
//$pdf->Cell(45,6,utf8_decode('Retos de liderazgo'),'LR',0,'C',true);
$pdf->SetXY(10,134+$y+$cuadroDos);
$pdf->SetFont('Arial','B',11);
$pdf->SetFillColor(255,255,255);
$pdf->Cell(45,6,utf8_decode('Nota Final de Programa'),'LTR',1,0,'C',false);
$pdf->SetXY(10,140+$y+$cuadroDos);
$pdf->SetFont('Arial','',8);
$pdf->SetFillColor(255,255,255);
$pdf->Cell(45,4,utf8_decode('(Promedio simple)'),'LBR',1,1,'L',false);
$pdf->SetXY(55,140+$y+$cuadroDos);
$pdf->Cell(72,4,utf8_decode(''),'LBR',1,1,'L',false);
$pdf->SetXY(127,140+$y+$cuadroDos);
$pdf->Cell(72,4,utf8_decode(''),'LBR',1,1,'L',false);

$pdf->SetXY(10,146+$y+$cuadroDos);
$pdf->SetFont('Arial','',7);
$pdf->SetFillColor(180,198,231);
$pdf->Cell(189,6,utf8_decode('* Promedio de las notas obtenidas por todos los participantes del programa Discovery, grupo 3.'),0,0,'L',true);
//*********************        CONSOLIDADO DE NOTAS       ********************************//


//*********************        DETALLE DE NOTAS POR ACTIVIDAD      ********************************//
$pdf->SetFont('Arial','B',11);
$pdf->SetXY(10,157+$y+$cuadroTres);
$pdf->Cell(40,6,utf8_decode('III. DETALLE DE NOTAS POR ACTIVIDAD'),0,1,'L');

$data = array( array(17.00,14.67, 15.38, 18.00, 16.96, 18.00, 16.40),
               array(17.27,13.56, 14.68, 18.00, 16.78, 20.00, 18.04),
               array('Pendiente', 'Pendiente', 'Pendiente', 'Pendiente', 'Pendiente', 'Pendiente', 'Pendiente') );
$long = array(20.5, 20.5,20.5, 20.5, 20.5, 20.5, 20.5);
$pdf->TableThree($data,55,$long, $y+$cuadroTres);

$pdf->SetFillColor(198,224,180);
$pdf->SetFont('Arial','',11);
$pdf->SetXY(10,183+$y+$cuadroTres);
$pdf->SetLineWidth(.5);
$pdf->Cell(45,6,utf8_decode('Módulo Líder'),'LTR',0,'C',true);
$pdf->SetXY(10,189+$y+$cuadroTres);
$pdf->Cell(45,6,utf8_decode('Módulo Colaborador'),'LR',0,'C',true);
$pdf->SetXY(10,195+$y+$cuadroTres);
$pdf->Cell(45,6,utf8_decode('Módulo Negocio'),'LBR',0,'C',true);
/*
$pdf->SetXY(10,185+$y+$cuadroTres);
$pdf->Cell(45,6,utf8_decode('Módulo Colaborador'),'LR',0,'C',true);
$pdf->SetXY(10,191+$y+$cuadroTres);
$pdf->Cell(45,6,utf8_decode('Módulo Negocio'),'LBR',0,'C',true);
*/
//*********************        DETALLE DE NOTAS POR ACTIVIDAD      ********************************//


//*********************        DETALLE DE NOTAS POR ACTIVIDAD      ********************************//
/*$pdf->SetFont('Arial','B',11);
$pdf->SetXY(10,203+$y+$cuadroCuatro);

$data = array( array('Pendiente', 'Pendiente', 'Pendiente', 'Pendiente', 'Pendiente') );
$long = array(20.5, 30.75,30.75, 30.75, 30.75);
$pdf->TableFour($data,55,$long, $y+$cuadroCuatro);

$pdf->SetFillColor(198,224,180);
$pdf->SetFont('Arial','',11);
$pdf->SetXY(10,215+$y+$cuadroCuatro);
$pdf->SetLineWidth(.5);
$pdf->Cell(45,6,utf8_decode('Módulo Integrador'),1,0,'C',true);*/
//*********************        DETALLE DE NOTAS POR ACTIVIDAD      ********************************//



//*********************        DETALLE DE NOTAS POR ACTIVIDAD      ********************************//
$pdf->SetFont('Arial','B',11);
$pdf->SetXY(10,230+$y+$cuadroCinco);

$data = array( array('Pendiente', 'Pendiente', 'Pendiente', 'Pendiente', 'Pendiente', 'Pendiente', 'Pendiente','Pendiente','Pendiente') );
$long = array(20.5, 20.5,20.5, 20.5, 20.5, 20.5, 20.5, 20.5, 20.5);
$pdf->TableFive($data,20,$long, $y+$cuadroCinco);

$pdf->SetFillColor(198,224,180);
$pdf->SetFont('Arial','',11);
$pdf->SetXY(2,250.2+$y+$cuadroCinco);
$pdf->SetLineWidth(.5);
$pdf->Cell(18,6,utf8_decode('Integral'),1,0,'C',true);
//*********************        DETALLE DE NOTAS POR ACTIVIDAD      ********************************//



$pdf->Output();
