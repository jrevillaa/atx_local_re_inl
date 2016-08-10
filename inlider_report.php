<?php

//ini_set('memory_limit', '1024M');
global $DB, $PAGE,$CFG;

require_once("../../config.php");
require('fpdf.php');
//require('html2pdf.php');
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/user/lib.php';
// Input params
$courseid = required_param('courseid', PARAM_INT);
$userid = required_param('userid', PARAM_INT);

$course = $DB->get_record("course", array("id" => $courseid), '*', MUST_EXIST);
require_course_login($course);

$context = context_course::instance($course->id);
//require_capability('mod/evaluation:viewallassessments', $context);


$main_url = new moodle_url('/blocks/inlider_report_challenge/inlider_report_challenge.php', array('id' => $courseid));

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}
require_login($course);


$context = context_course::instance($course->id);
require_capability('gradereport/user:view', $context);

if (empty($userid)) {
    require_capability('moodle/grade:viewall', $context);

} else {
    if (!$DB->get_record('user', array('id'=>$userid, 'deleted'=>0)) or isguestuser($userid)) {
        print_error('invaliduser');
    }
}

$access = false;
if (has_capability('moodle/grade:viewall', $context)) {
    //ok - can view all course grades
    $access = true;

} else if ($userid == $USER->id and has_capability('moodle/grade:view', $context) and $course->showgrades) {
    //ok - can view own grades
    $access = true;

} else if (has_capability('moodle/grade:viewall', context_user::instance($userid)) and $course->showgrades) {
    // ok - can view grades of this user- parent most probably
    $access = true;
}

if (!$access) {
    // no access to grades!
    print_error('nopermissiontoviewgrades', 'error',  $CFG->wwwroot.'/course/view.php?id='.$courseid);
}


$gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'user', 'courseid'=>$courseid, 'userid'=>$userid));


$userid = $USER->id;



$report = new grade_report_user($courseid, $gpr, $context, $userid);


$user_picture=new user_picture($report->user);
$src=$user_picture->get_url($PAGE); 

$output = new stdClass();
$contextu = context_user::instance($userid);
//$output->image = htmlspecialchars($src);
$output->image = $CFG->wwwroot . "/user/pix.php/" . $userid . "/f1.jpg";
//$userr = $DB->get_record('user',array('id' => $userid));
//$output->image = $OUTPUT->user_picture($userr);
$output->fullname = fullname($report->user);

$size = array('large' => 'f1', 'small' => 'f2');

/*
echo "<pre>";
print_r(  $output );
echo "</pre>--------------------------------";*/

$sql = "SELECT * FROM {grade_categories} WHERE courseid = " . $courseid . " AND parent != ''";
$categories_courses = $DB->get_records_sql($sql);
/*echo "<pre>";
print_r($categories_courses);
echo "</pre>";*/
$cat_lide = false;
$cat_cola = false;
$cat_inte = false;
$cat_reto = false;


$out = array();
foreach ($categories_courses as $value) {

    $cat = 0;

    $sql = "SELECT 
       gi.idnumber AS idnumber,
       gi.id AS itemid,
       gi.itemname AS item,
       ROUND(gg.finalgrade,2) AS final_grade
    FROM {grade_grades} gg
    JOIN {user} u ON gg.userid = u.id
    JOIN {grade_items} gi ON gg.itemid = gi.id
    JOIN {grade_categories} gc ON gi.categoryid = gc.id
    JOIN {course} c ON gi.courseid = c.id
    WHERE  u.id = '" . $userid . "'
    AND gi.hidden = '0'
    AND gi.categoryid = '" . $value->id . "'
    AND c.id = '" . $courseid . "'
    ORDER BY c.idnumber, u.username, gi.itemname, gg.timemodified";
    $grades = $DB->get_records_sql($sql);
    /*echo "<br>" . $sql . "<br>";
    echo "<pre>";
    print_r($grades);
    echo "</pre>";*/
    $glo_tm = false;
    $glo_grad = false;
    $ult = false;
    if($grades != array()) {
        foreach ($grades as $key => $grade) {

            $prome_grade = $DB->get_records('grade_grades', array('itemid' => $grade->itemid), '', 'id,userid,finalgrade');
            /*echo "<pre>";
            print_r($prome_grade);
            echo "</pre>";*/
            if($prome_grade == array()){
                /*echo "<pre>";
                print_r($grade);
                echo "</pre>";*/
                continue;
            }
            $tm = false;
            $promedio_temp = 0;
            $coun = 0;
            foreach ($prome_grade as $kii=>$tmp) {




                $userrol = $DB->get_record('role_assignments', array('contextid' => $context->id, 'roleid' => 5, 'userid' => $tmp->userid));

                $sql_status = "SELECT mue.id,mue.status
                            FROM mdl_user_enrolments AS mue
                            INNER JOIN mdl_enrol AS me ON (mue.enrolid = me.id)
                            WHERE mue.userid = " . $tmp->userid . " AND me.courseid = " . $courseid;

                $ue = $DB->get_record_sql($sql_status);

                if (is_object($userrol) && $ue->status == 0 && !empty($tmp->finalgrade)) {

                    if (empty($tmp->finalgrade)) {
                        $tm = true;
                        if(strtoupper(substr($grades[$key]->idnumber, 0, 4)) != 'ASIS'){
                            $glo_tm = true;
                        }
                    }
                    $tmp->finalgrade = round($tmp->finalgrade);
                    $promedio_temp = $promedio_temp + $tmp->finalgrade;
                    $coun++;
                }


            }
            if(empty($grades[$key]->final_grade)){
                $grades[$key]->final_grade = 'Pendiente';
                $glo_grad = true;
            }
            $grades[$key]->promedio = 'Pendiente';
            if($coun == 0){
                /*echo "<pre>";
                print_r($grade);
                echo "</pre>";*/
                $ult = true;
                continue;
            }

            $promedio_temp = round($promedio_temp / $coun,2);
            //if ($tm) {
            //    $grades[$key]->promedio = 'Pendiente';
            //    $cat = 1;
            //} else {
                $grades[$key]->promedio = round($promedio_temp,2);
                $grades[$key]->promedio_ocunt = $coun;

            //}

            //$grades[$key]->promedio = ($tm) ? 'Pendiente' : round($promedio_temp);

        }


        $grades = array_values($grades);

        $sql = "SELECT gi.idnumber AS idnumber,
           gi.itemname AS item,
           gi.id AS itemid,
           ROUND(gg.finalgrade,2) AS final_grade
    FROM mdl_grade_grades gg
    JOIN mdl_user u ON gg.userid = u.id
    JOIN mdl_grade_items gi ON gg.itemid = gi.id
    JOIN mdl_course c ON gi.courseid = c.id
    WHERE  u.id = '" . $userid . "'
    AND gi.hidden = '0'
    AND gi.categoryid IS NULL 
    AND gi.iteminstance = '" . $value->id . "'
    AND c.id = '" . $courseid . "'
    ";
        $prome = $DB->get_record_sql($sql);


        $prome_grade = $DB->get_records('grade_grades', array('itemid' => $prome->itemid), '', 'id,userid,finalgrade');


        //$tm = false;
        $promedio_temp = 0;
        $coun = 0;
        foreach ($prome_grade as $ke => $tmp) {
            $userrol = $DB->get_record('role_assignments', array('contextid' => $context->id, 'roleid' => 5, 'userid' => $tmp->userid));


            $sql_status = "SELECT mue.id,mue.status
                            FROM mdl_user_enrolments AS mue
                            INNER JOIN mdl_enrol AS me ON (mue.enrolid = me.id)
                            WHERE mue.userid = " . $tmp->userid . " AND me.courseid = " . $courseid;

            $ue = $DB->get_record_sql($sql_status);

            if (is_object($userrol) && $ue->status == 0 && !empty($tmp->finalgrade)) {

                $coun++;
                $tmp->finalgrade = round($tmp->finalgrade);
                $promedio_temp = $promedio_temp + $tmp->finalgrade;
            }


        }
        if($coun == 0){
            continue;
        }
        $promedio_temp = round($promedio_temp / $coun,2);
        if($glo_grad){
            $prome->final_grade = 'Pendiente';
        }
        //if ($glo_tm) {
        //    $prome->promedio = 'Pendiente';
        //    $cat = 1;
        //} else {
            $prome->promedio = round($promedio_temp,2);
            $prome->promedio_count = $coun;
        //}
        //$prome->promedio = ($tm) ? 'Pendiente' : round($promedio_temp);

        if($ult){
            $prome->promedio = 'Pendiente';
            $ult = false;
        }

        $out[$value->fullname] = array('grades' => $grades, 'average' => $prome, 'pendiente' => $cat);


    }
}

if(!isset($out['MÓDULO INTEGRAL'])){
    $objParti = new stdClass();
    $objParti->idnumber = 'PAR_INT_1231';
    $objParti->itemid = 231;
    $objParti->item = '-------------------';
    $objParti->final_grade = 'Pendiente';
    $objParti->promedio = 'Pendiente';

    $objExa = new stdClass();
    $objExa->idnumber = 'EXA_INT_1231';
    $objExa->itemid = 231;
    $objExa->item = '--------------------------';
    $objExa->final_grade = 'Pentiente';
    $objExa->promedio = 'Pendiente';

    $objAve = new stdClass();

    $objAve->final_grade = 'Pendiente';
    $objAve->promedio = 'Pendiente';

    $temp = array('grades' => array($objParti,$objExa), 'average' => $objAve, 'pendiente' => 1);
    $out['MÓDULO INTEGRAL'] = $temp;
}

if(!isset($out['MÓDULO LÍDER'])){
    $objParti = new stdClass();
    $objParti->idnumber = 'PAR_LID_1231';
    $objParti->itemid = 231;
    $objParti->item = '-------- (30%)';
    $objParti->final_grade = 'Pendiente';
    $objParti->promedio = 'Pendiente';

    $objExa = new stdClass();
    $objExa->idnumber = 'EXA_LID_sss';
    $objExa->itemid = 231;
    $objExa->item = '-----------------';
    $objExa->final_grade = 'Pentiente';
    $objExa->promedio = 'Pendiente';

    $objMod = new stdClass();
    $objMod->idnumber = 'MOD_LID_sss';
    $objMod->itemid = 231;
    $objMod->item = '-----------------(30%)';
    $objMod->final_grade = 'Pentiente';
    $objMod->promedio = 'Pendiente';

    $objAve = new stdClass();

    $objAve->final_grade = 'Pendiente';
    $objAve->promedio = 'Pendiente';

    $temp = array('grades' => array($objParti,$objExa,$objMod), 'average' => $objAve, 'pendiente' => 1);
    $out['MÓDULO LÍDER'] = $temp;
}

if(!isset($out['MÓDULO NEGOCIO'])){
    $objParti = new stdClass();
    $objParti->idnumber = 'PAR_NEG_1231';
    $objParti->itemid = 231;
    $objParti->item = '--------------- (30%)';
    $objParti->final_grade = 'Pendiente';
    $objParti->promedio = 'Pendiente';

    $objExa = new stdClass();
    $objExa->idnumber = 'EXA_NEG_AAA';
    $objExa->itemid = 231;
    $objExa->item = '------------------ (30%)';
    $objExa->final_grade = 'Pentiente';
    $objExa->promedio = 'Pendiente';

    $objMod = new stdClass();
    $objMod->idnumber = 'MOD_NEG_ss';
    $objMod->itemid = 231;
    $objMod->item = '---------------------------------- (30%)';
    $objMod->final_grade = 'Pentiente';
    $objMod->promedio = 'Pendiente';

    $objAve = new stdClass();

    $objAve->final_grade = 'Pendiente';
    $objAve->promedio = 'Pendiente';

    $temp = array('grades' => array($objParti,$objExa,$objMod), 'average' => $objAve, 'pendiente' => 1);
    $out['MÓDULO NEGOCIO'] = $temp;
}

if(!isset($out['MÓDULO COLABORADOR'])){
    $objParti = new stdClass();
    $objParti->idnumber = 'PAR_COL_1231';
    $objParti->itemid = 231;
    $objParti->item = '----------------------------- (30%)';
    $objParti->final_grade = 'Pendiente';
    $objParti->promedio = 'Pendiente';

    $objExa = new stdClass();
    $objExa->idnumber = 'EXA_COL_ss';
    $objExa->itemid = 231;
    $objExa->item = '----------------------------------- (30%)';
    $objExa->final_grade = 'Pentiente';
    $objExa->promedio = 'Pendiente';

    $objMod = new stdClass();
    $objMod->idnumber = 'MOD_COL_132';
    $objMod->itemid = 231;
    $objMod->item = '----------------------------- (30%)';
    $objMod->final_grade = 'Pentiente';
    $objMod->promedio = 'Pendiente';

    $objAve = new stdClass();

    $objAve->final_grade = 'Pendiente';
    $objAve->promedio = 'Pendiente';

    $temp = array('grades' => array($objParti,$objExa,$objMod), 'average' => $objAve, 'pendiente' => 1);
    $out['MÓDULO COLABORADOR'] = $temp;
}

if(!isset($out['RETOS DE LIDERAZGO'])){
    $objParti = new stdClass();
    $objParti->idnumber = 'RETO1_1231';
    $objParti->itemid = 231;
    $objParti->item = '----------------------------- (30%)';
    $objParti->final_grade = 'Pendiente';
    $objParti->promedio = 'Pendiente';

    $objExa = new stdClass();
    $objExa->idnumber = 'RETO2_';
    $objExa->itemid = 231;
    $objExa->item = '----------------------------------- (30%)';
    $objExa->final_grade = 'Pentiente';
    $objExa->promedio = 'Pendiente';

    $objMod = new stdClass();
    $objMod->idnumber = 'RETO3';
    $objMod->itemid = 231;
    $objMod->item = '----------------------------- (30%)';
    $objMod->final_grade = 'Pentiente';
    $objMod->promedio = 'Pendiente';



    $objAve = new stdClass();
    $objAve->final_grade = 'Pendiente';
    $objAve->promedio = 'Pendiente';

    $temp = array('grades' => array($objParti,$objExa,$objMod), 'average' => $objAve, 'pendiente' => 1);
    $out['RETOS DE LIDERAZGO'] = $temp;
}

/*
echo "<pre>";
print_r($out);
echo "</pre>";*/
/****************ASISTENCIA**********************************/

$atten = $DB->get_record('modules', array('name' => 'attendance'));

if(!is_object($atten)){
    echo "Error, no tienen instalado módulo de Asistencias";
    exit;
}

$asis_sql = "SELECT att.id, att.course, att.name, att.grade, cm.idnumber
             FROM {attendance} att
             INNER JOIN {course_modules} cm ON att.id = cm.instance
             WHERE cm.module = " . $atten->id . "
             AND att.course =" . $courseid;

$as_mods = $DB->get_records_sql($asis_sql);
foreach($as_mods AS $value){
   /* echo "<pre>";
    print_r($value);
    echo "</pre>";*/
    $sql = "SELECT * FROM {attendance_sessions} WHERE attendanceid =" . $value->id;
    $sessions_att = $DB->get_records_sql($sql);
    $asist_total = $value->grade;
    $asis_act = 0;
    $is_pedient = false;
    $diferencia = '';
    switch(strtoupper(substr($value->idnumber, 0, 9))){
        case 'ASIS_LIDE':
            $diferencia = 'lide';
            foreach($sessions_att AS $val){
                $sql_grade = "SELECT al.id, al.sessionid, studentid, statusid, ats.acronym, ats.description, ats.grade
                          FROM mdl_attendance_log al
                          INNER JOIN mdl_attendance_statuses ats ON ats.id = al.statusid
                          WHERE al.sessionid =" .$val->id .
                        " AND al.studentid =" . $userid;
                $point = $DB->get_record_sql($sql_grade);
                if(is_object($point) && $point->grade > 0){
                    $asis_act = $asis_act + (int)$point->grade;
                }
                if($val->lasttakenby == 0){
                    $is_pedient = true;
                }
            }
             break;
        case 'ASIS_COLA':
            $diferencia = 'cola';
            foreach($sessions_att AS $val){
                $sql_grade = "SELECT al.id, al.sessionid, studentid, statusid, ats.acronym, ats.description, ats.grade
                          FROM mdl_attendance_log al
                          INNER JOIN mdl_attendance_statuses ats ON ats.id = al.statusid
                          WHERE al.sessionid =" .$val->id .
                    " AND al.studentid =" . $userid;
                $point = $DB->get_record_sql($sql_grade);
                if(is_object($point) && $point->grade > 0){
                    $asis_act = $asis_act + (int)$point->grade;
                }
                if($val->lasttakenby == 0){
                    $is_pedient = true;
                }
            }
        break;
        case 'ASIS_NEGO':
            $diferencia = 'nego';
            foreach($sessions_att AS $val){
                $sql_grade = "SELECT al.id, al.sessionid, studentid, statusid, ats.acronym, ats.description, ats.grade
                          FROM mdl_attendance_log al
                          INNER JOIN mdl_attendance_statuses ats ON ats.id = al.statusid
                          WHERE al.sessionid =" .$val->id .
                    " AND al.studentid =" . $userid;
                $point = $DB->get_record_sql($sql_grade);
                if(is_object($point) && $point->grade > 0){
                    $asis_act = $asis_act + (int)$point->grade;
                }
                if($val->lasttakenby == 0){
                    $is_pedient = true;
                }
            }
        break;
        case 'ASIS_INTE':
            $diferencia = 'inte';
            foreach($sessions_att AS $val){
                $sql_grade = "SELECT al.id, al.sessionid, studentid, statusid, ats.acronym, ats.description, ats.grade
                          FROM mdl_attendance_log al
                          INNER JOIN mdl_attendance_statuses ats ON ats.id = al.statusid
                          WHERE al.sessionid =" .$val->id .
                    " AND al.studentid =" . $userid;
                $point = $DB->get_record_sql($sql_grade);
                if(is_object($point) && $point->grade > 0){
                    $asis_act = $asis_act + (int)$point->grade;
                }
                if($val->lasttakenby == 0){
                    $is_pedient = true;
                }
            }

        break;
    }

    $promedio_asistencia = ($is_pedient) ? 'Pendiente' : round(($asis_act*100)/$asist_total) . '%';
    $final_asistencia[$diferencia] =array(
                                'total_horas' => $asist_total,
                                'sesiones' => count($sessions_att) ,
                                'asistencias' => $asis_act,
                                'prom' => $promedio_asistencia
                        );



}/*

echo "<pre>";
print_r($final_asistencia);
echo "</pre>";*/



/****************ASISTENCIA**********************************/


ini_set('display_errors', 'On');
error_reporting(E_ALL);

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

class PDF extends FPDF
{

    var $B;
    var $I;
    var $U;
    var $HREF;
    var $issetfont;
    var $issetcolor;

    public function __construct()
    {
        parent::__construct();
    }

    function PDF()
    {

        //Initialization
        $this->B=0;
        $this->I=0;
        $this->U=0;
        $this->HREF='';
        $this->issetfont=false;
        $this->issetcolor=false;
    }

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
        $this->SetX($x);
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
        $this->SetX($x);
        $this->SetLineWidth(.5);
        $this->SetFont('Arial','B',9);
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C');
        $this->SetFont('Arial','B',11);
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
        $this->Cell(41,4,utf8_decode('Promedio de Particitación'),1,0,'C');
        $this->Cell(41,4,utf8_decode('Curso Virtual'),1,0,'C');
        $this->Cell(41,4,utf8_decode('Examen de Módulo'),1,0,'C');
        $this->Ln();

        $this->SetX($x);
        $this->Cell(20.5,4,utf8_decode('Nota Final'),'LR',0,'C');
        $this->Cell(41,4,utf8_decode('30%'),1,0,'C');
        $this->Cell(41,4,utf8_decode('40%'),1,0,'C');
        $this->Cell(41,4,utf8_decode('30%'),1,0,'C');
        $this->Ln();
        $this->SetXY($x,171+$y);
        $this->SetTextColor(255);
        $this->MultiCell(20.5,4,utf8_decode('Nota Participante'),'LBR','C');
        $this->SetXY(75.5,171+$y);
        $this->SetTextColor(0);
        $this->MultiCell(20.5,4,utf8_decode('Nota Participante'),1,'C');
        $this->SetXY(96,171+$y);
        $this->MultiCell(20.5,4,utf8_decode('Promedio Grupo'),1,'C');
        $this->SetXY(116.5,171+$y);
        $this->MultiCell(20.5,4,utf8_decode('Nota Participante'),1,'C');
        $this->SetXY(137,171+$y);
        $this->MultiCell(20.5,4,utf8_decode('Promedio Grupo'),1,'C');
        $this->SetXY(157.5,171+$y);
        $this->MultiCell(20.5,4,utf8_decode('Nota Participante'),1,'C');
        $this->SetXY(178,171+$y);
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
        $this->Cell(61.5,4,utf8_decode('Participación en Clase'),1,0,'C');
        $this->Cell(61.5,4,utf8_decode('Examen de Comprobación de Lectura'),1,0,'C');
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
    }

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
        $this->Cell(41,4,utf8_decode('Reto de Liderazgo 1'),1,0,'C');
        //$this->Cell(20.5,4,utf8_decode('uno'),1,0,'C');
        $this->Cell(41,4,utf8_decode('Reto de Liderazgo 2'),1,0,'C');
        //$this->Cell(20.5,4,utf8_decode('uno'),1,0,'C');
        $this->Cell(41,4,utf8_decode('Reto de Liderazgo 3'),1,0,'C');

        $this->Ln();
        $this->SetXY($x,234.2+$y);
        //$this->SetTextColor(255);
        $this->MultiCell(20.5,8,utf8_decode('Nota Final'),'LBR','C');
        $this->SetXY(75.5,234.2+$y);
        //$this->SetTextColor(0);
        $this->MultiCell(20.5,4,utf8_decode('Nota Participante'),1,'C');
        $this->SetXY(96,234.2+$y);
        $this->MultiCell(20.5,4,utf8_decode('Promedio Grupo'),1,'C');
        $this->SetXY(116.5,234.2+$y);
        $this->MultiCell(20.5,4,utf8_decode('Nota Participante'),1,'C');
        $this->SetXY(137,234.2+$y);
        $this->MultiCell(20.5,4,utf8_decode('Promedio Grupo'),1,'C');
        $this->SetXY(157.5,234.2+$y);
        $this->MultiCell(20.5,4,utf8_decode('Nota Participante'),1,'C');
        $this->SetXY(178,234.2+$y);
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

    function WriteHTML($html)
    {
        //HTML parser
        $html=strip_tags($html,"<b><u><i><a><img><p><br><strong><em><font><tr><blockquote>"); //supprime tous les tags sauf ceux reconnus
        $html=str_replace("\n",' ',$html); //remplace retour � la ligne par un espace
        $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE); //�clate la cha�ne avec les balises
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
                    //$this->Image($attr['SRC'], $this->GetX(), $this->GetY(), px2mm($attr['WIDTH']), px2mm($attr['HEIGHT']),'JPG');
                    $this->Image($attr['SRC'],15,52,30,0,'JPG');
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
                if (isset($attr['FACE'])) {
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


}



$nombre_curso = $course->fullname;
$reporte_titulo = 'REPORTE DE RESULTADOS';
$reporte_user = $output->fullname;
$url_img = $output->image;

/////////////////////ASISTENCIA////////////////////////////////////

$asistencia_lider = array(
    $final_asistencia['lide']['total_horas'],
    $final_asistencia['lide']['asistencias'],
    $final_asistencia['lide']['prom']
);

$asistencia_colaborador = array(
    $final_asistencia['cola']['total_horas'],
    $final_asistencia['cola']['asistencias'],
    $final_asistencia['cola']['prom']
);

$asistencia_negocio = array(
    $final_asistencia['nego']['total_horas'],
    $final_asistencia['nego']['asistencias'],
    $final_asistencia['nego']['prom']
);

$asistencia_integral = array(
    $final_asistencia['inte']['total_horas'],
    $final_asistencia['inte']['asistencias'],
    $final_asistencia['inte']['prom']
);



/////////////////////ASISTENCIA////////////////////////////////////

///////////////////////////TABLA II ///////////////////////////////////////////
$t1_lider_nota = $out['MÓDULO LÍDER']['average']->final_grade;
$t1_lider_promedio = $out['MÓDULO LÍDER']['average']->promedio;

$t1_cola_nota = $out['MÓDULO COLABORADOR']['average']->final_grade;
$t1_cola_promedio = $out['MÓDULO COLABORADOR']['average']->promedio;

$t1_negocio_nota = $out['MÓDULO NEGOCIO']['average']->final_grade;
$t1_negocio_promedio = $out['MÓDULO NEGOCIO']['average']->promedio;

$t1_inte_nota = $out['MÓDULO INTEGRAL']['average']->final_grade;
$t1_inte_promedio = $out['MÓDULO INTEGRAL']['average']->promedio;

$t1_reto_nota = $out['RETOS DE LIDERAZGO']['average']->final_grade;
$t1_reto_promedio = $out['RETOS DE LIDERAZGO']['average']->promedio;

if($t1_lider_nota == 'Pendiente' ||
   $t1_cola_nota == 'Pendiente' ||
   $t1_negocio_nota == 'Pendiente' ||
   $t1_inte_nota == 'Pendiente' ||
   $t1_reto_nota == 'Pendiente'){

    $t1_nota_final = 'Pendiente';
    $t1_promedio_final = 'Pendiente';
}else if($t1_lider_promedio == 'Pendiente' ||
   $t1_cola_promedio == 'Pendiente' ||
   $t1_negocio_promedio == 'Pendiente' ||
   $t1_inte_promedio == 'Pendiente' ||
   $t1_reto_promedio == 'Pendiente'){

    $t1_nota_final = round( ($t1_lider_nota + $t1_cola_nota + $t1_negocio_nota + $t1_inte_nota + $t1_reto_nota) / 5 );
    $t1_promedio_final = 'Pendiente';
}else{
    $t1_nota_final = round( ($t1_lider_nota + $t1_cola_nota + $t1_negocio_nota + $t1_inte_nota + $t1_reto_nota) / 5 );
    $t1_promedio_final = round( ($t1_lider_promedio + $t1_cola_promedio + $t1_negocio_promedio + $t1_inte_promedio + $t1_reto_promedio) / 5 );
}
///////////////////////////TABLA II ///////////////////////////////////////////


///////////////////////////TABLA III ///////////////////////////////////////////
foreach($out as $ki=>$nombre){

    switch($ki){
        case 'MÓDULO LÍDER';
            foreach($nombre['grades'] as $nota){


                switch(strtoupper(substr($nota->idnumber, 0, 7))){
                    case 'MOD_LID':
                        $t2_lider_nota_curso = $nota->final_grade;
                        $t2_lider_promedio_curso = $nota->promedio;
                        break;
                    case 'PAR_LID':
                        $t2_lider_nota_promedio = $nota->final_grade;
                        $t2_lider_promedio_promedio = $nota->promedio;
                        break;
                    case 'EXA_LID':
                        $t2_lider_nota_modulo = $nota->final_grade;
                        $t2_lider_promedio_modulo = $nota->promedio;
                        break;
                }
            }
            break;
        case 'MÓDULO COLABORADOR';
            foreach($nombre['grades'] as $nota){

                switch(strtoupper(substr($nota->idnumber, 0, 7))) {
                    case 'MOD_COL':
                        $t2_cola_nota_curso = $nota->final_grade;
                        $t2_cola_promedio_curso = $nota->promedio;
                        break;
                    case 'PAR_COL':
                        $t2_cola_nota_promedio = $nota->final_grade;
                        $t2_cola_promedio_promedio = $nota->promedio;
                        break;
                    case 'EXA_COL':
                        $t2_cola_nota_modulo = $nota->final_grade;
                        $t2_cola_promedio_modulo = $nota->promedio;
                        break;
                }
            }

            break;
        case 'MÓDULO NEGOCIO';
            foreach($nombre['grades'] as $nota){

                switch(strtoupper(substr($nota->idnumber, 0, 7))) {
                    case 'MOD_NEG':
                        $t2_nego_nota_curso = $nota->final_grade;
                        $t2_nego_promedio_curso = $nota->promedio;
                        break;
                    case 'PAR_NEG':
                        $t2_nego_nota_promedio = $nota->final_grade;
                        $t2_nego_promedio_promedio = $nota->promedio;
                        break;
                    case 'EXA_NEG':
                        $t2_nego_nota_modulo = $nota->final_grade;
                        $t2_nego_promedio_modulo = $nota->promedio;
                        break;
                }
            }
            break;
        case 'MÓDULO INTEGRAL';
            foreach($nombre['grades'] as $nota){
                /*echo "<pre>";
                print_r($nota);
                echo "</pre>";*/
                switch(strtoupper(substr($nota->idnumber, 0, 7))) {
                    case 'PAR_INT':
                        $t3_inte_nota_parti = $nota->final_grade;
                        $t3_inte_promedio_parti = $nota->promedio;
                        break;
                    case 'EXA_INT':
                        $t3_inte_nota_lectura = $nota->final_grade;
                        $t3_inte_promedio_lectura = $nota->promedio;
                        break;
                }
            }
            break;
        case 'RETOS DE LIDERAZGO';
            foreach($nombre['grades'] as $nota){
                /*echo "<pre>";
                print_r($nota);
                echo "<br>";
                print_r(strtoupper(substr($nota->idnumber, 0, 5)));
                echo "</pre>";*/
                switch(strtoupper(substr($nota->idnumber, 0, 5))) {
                    case 'RETO1':
                        $t4_reto_nota_reto1 = $nota->final_grade;
                        $t4_reto_promedio_reto1 = $nota->promedio;
                        break;
                    case 'RETO2':
                        $t4_reto_nota_reto2 = $nota->final_grade;
                        $t4_reto_promedio_reto2 = $nota->promedio;
                        break;
                    case 'RETO3':
                        $t4_reto_nota_reto3 = $nota->final_grade;
                        $t4_reto_promedio_reto3 = $nota->promedio;
                        break;
                }
            }
            break;
    }

}

if(!isset($t4_reto_nota_reto2)){
    $t4_reto_nota_reto2 = 'Pendiente';
    $t4_reto_promedio_reto2 = 'Pendiente';
}

if(!isset($t4_reto_nota_reto3)){
    $t4_reto_nota_reto3 = 'Pendiente';
    $t4_reto_promedio_reto3 = 'Pendiente';
}


///////////////////////////TABLA V ///////////////////////////////////////////
/*$t4_reto_nota_reto1 = $out['MÓDULO COLABORADOR']['grades'][1]->final_grade;
$t4_reto_promedio_reto1 = $out['MÓDULO COLABORADOR']['grades'][1]->promedio;

$t4_reto_nota_reto2 = $out['MÓDULO COLABORADOR']['grades'][0]->final_grade;
$t4_reto_promedio_reto2 = $out['MÓDULO COLABORADOR']['grades'][0]->promedio;

$t4_reto_nota_reto3 = $out['MÓDULO COLABORADOR']['grades'][2]->final_grade;
$t4_reto_promedio_reto3 = $out['MÓDULO COLABORADOR']['grades'][2]->promedio;*/
///////////////////////////TABLA V ///////////////////////////////////////////




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
try{
    $pdf->Image($url_img,15,52,30,0,'JPG');
}catch (Exception $e){
    try{
        $pdf->Image($url_img,15,52,30,0,'JPEG');
    }catch(Exception $ue){
        $pdf->Image($url_img,15,52,30,0,'PNG');
    }
}
//$pdf->WriteHTML($url_img);



$pdf->SetFont('Arial','B',11);
$pdf->SetXY(10, 85);
$pdf->MultiCell(40,4,utf8_decode($reporte_user),0,'C',false);


//*********************        ASISTENCIA      ********************************//
$pdf->SetXY(58,54);
$pdf->Cell(40,6,utf8_decode('I. ASISTENCIA'),0,1,'L');

$pdf->SetFillColor(198,224,180);
$pdf->SetFont('Arial','',11);
$pdf->SetXY(58,66);
$pdf->SetLineWidth(.5);
$pdf->Cell(40,6,utf8_decode('Módulo Líder'),'LTR',0,'C',true);
$pdf->SetXY(58,72);
$pdf->Cell(40,6,utf8_decode('Módulo Colaborador'),'LR',0,'C',true);
$pdf->SetXY(58,78);
$pdf->Cell(40,6,utf8_decode('Módulo Negocio'),'LR',0,'C',true);
$pdf->SetXY(58,84);
$pdf->Cell(40,6,utf8_decode('Módulo Integral'),'LBR',0,'C',true);
//$this->SetLineWidth(.5);
$pdf->SetY(59);
$header = array('DURACIÓN (Hrs)', 'ASISTENCIA (Hrs)', 'ASISTENCIA');
$data = array( $asistencia_lider , $asistencia_colaborador , $asistencia_negocio , $asistencia_integral);
$long = array(35, 38, 29);
$pdf->TableOne($header,$data,97,$long);
//*********************        ASISTENCIA      ********************************//


//*********************        CONSOLIDADO DE NOTAS       ********************************//
$pdf->SetFont('Arial','B',11);
$pdf->SetXY(10,97+$y+$cuadroDos);
$pdf->Cell(40,6,utf8_decode('II. CONSOLIDADO DE NOTAS'),0,1,'L');
//$pdf->SetY(58);
$header = array('Nota Final del Módulo', 'Promedio del ' . $nombre_curso . '*');

$data = array( 
            array($t1_lider_nota,$t1_lider_promedio) , 
            array($t1_cola_nota,$t1_cola_promedio) , 
            array($t1_negocio_nota,$t1_negocio_promedio) , 
            array($t1_inte_nota,$t1_inte_promedio) , 
            array($t1_reto_nota,$t1_reto_promedio) , 
            array($t1_nota_final,$t1_promedio_final) , 
        );
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
$pdf->Cell(45,6,utf8_decode('Módulo Integral'),'LR',0,'C',true);
$pdf->SetXY(10,134+$y+$cuadroDos);
$pdf->Cell(45,6,utf8_decode('Retos de Liderazgo'),'LR',0,'C',true);
$pdf->SetXY(10,140+$y+$cuadroDos);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(45,6,utf8_decode('Nota Final'),1,0,'C',false);
$pdf->SetXY(10,146+$y+$cuadroDos);
$pdf->SetFont('Arial','',7);
$pdf->SetFillColor(180,198,231);
$pdf->Cell(189,6,utf8_decode('* Promedio de las notas obtenidas por todos los participantes del programa ' . $nombre_curso),0,0,'L',true);
//*********************        CONSOLIDADO DE NOTAS       ********************************//


//*********************        DETALLE DE NOTAS POR ACTIVIDAD      ********************************//
$pdf->SetFont('Arial','B',11);
$pdf->SetXY(10,157+$y+$cuadroTres);
$pdf->Cell(40,6,utf8_decode('III. DETALLE DE NOTAS POR ACTIVIDAD'),0,1,'L');


$data = array( array($t1_lider_nota,$t2_lider_nota_promedio, $t2_lider_promedio_promedio, $t2_lider_nota_curso, $t2_lider_promedio_curso, $t2_lider_nota_modulo, $t2_lider_promedio_modulo),
               array($t1_cola_nota,$t2_cola_nota_promedio, $t2_cola_promedio_promedio, $t2_cola_nota_curso, $t2_cola_promedio_curso, $t2_cola_nota_modulo, $t2_cola_promedio_modulo),
               array($t1_negocio_nota, $t2_nego_nota_promedio, $t2_nego_promedio_promedio, $t2_nego_nota_curso, $t2_nego_promedio_curso, $t2_nego_nota_modulo, $t2_nego_promedio_modulo) );
$long = array(20.5, 20.5,20.5, 20.5, 20.5, 20.5, 20.5);
$pdf->TableThree($data,55,$long, $y+$cuadroTres);

$pdf->SetFillColor(198,224,180);
$pdf->SetFont('Arial','',11);
$pdf->SetXY(10,179+$y+$cuadroTres);
$pdf->SetLineWidth(.5);
$pdf->Cell(45,6,utf8_decode('Módulo Líder'),'LTR',0,'C',true);
$pdf->SetXY(10,185+$y+$cuadroTres);
$pdf->Cell(45,6,utf8_decode('Módulo Colaborador'),'LR',0,'C',true);
$pdf->SetXY(10,191+$y+$cuadroTres);
$pdf->Cell(45,6,utf8_decode('Módulo Negocio'),'LBR',0,'C',true);
//*********************        DETALLE DE NOTAS POR ACTIVIDAD      ********************************//


//*********************        DETALLE DE NOTAS POR ACTIVIDAD      ********************************//
$pdf->SetFont('Arial','B',11);
$pdf->SetXY(10,203+$y+$cuadroCuatro);


$data = array( array($t1_inte_nota, $t3_inte_nota_parti, $t3_inte_promedio_parti, $t3_inte_nota_lectura, $t3_inte_promedio_lectura) );
$long = array(20.5, 30.75,30.75, 30.75, 30.75);
$pdf->TableFour($data,55,$long, $y+$cuadroCuatro);

$pdf->SetFillColor(198,224,180);
$pdf->SetFont('Arial','',11);
$pdf->SetXY(10,215+$y+$cuadroCuatro);
$pdf->SetLineWidth(.5);
$pdf->Cell(45,6,utf8_decode('Módulo Integral'),1,0,'C',true);
//*********************        DETALLE DE NOTAS POR ACTIVIDAD      ********************************//



//*********************        DETALLE DE NOTAS POR ACTIVIDAD      ********************************//
$pdf->SetFont('Arial','B',11);
$pdf->SetXY(10,230+$y+$cuadroCinco);



$data = array( array($t1_reto_nota, $t4_reto_nota_reto1, $t4_reto_promedio_reto1, $t4_reto_nota_reto2, $t4_reto_promedio_reto2, $t4_reto_nota_reto3 , $t4_reto_promedio_reto3) );
$long = array(20.5, 20.5,20.5, 20.5, 20.5, 20.5, 20.5);
$pdf->TableFive($data,55,$long, $y+$cuadroCinco);

$pdf->SetFillColor(198,224,180);
$pdf->SetFont('Arial','',11);
$pdf->SetXY(10,242+$y+$cuadroCinco);
$pdf->SetLineWidth(.5);
$pdf->Cell(45,6,utf8_decode('Retos de Liderazgo'),1,0,'C',true);
//*********************        DETALLE DE NOTAS POR ACTIVIDAD      ********************************//


$pdf->Output();