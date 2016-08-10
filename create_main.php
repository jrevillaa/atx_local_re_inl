<?php
global $DB, $PAGE, $OUTPUT;

require_once("../../config.php");
require_once($CFG->libdir.'/adminlib.php');
include('forms.php');
include('lib.php');

admin_externalpage_setup('blockinlider_report_challenge');
$context = context_system::instance();
require_login();
require_capability('block/inlider_report_challenge:config',$context);

$main_url = new moodle_url('/blocks/inlider_report_challenge/create_main.php');

$mform = new inlider_report_challenge_create_main($main_url);

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
  $returnurl = new moodle_url('/blocks/inlider_report_challenge/admin.php');
  redirect($returnurl);
} else if ($data = $mform->get_data()) {

  

  $main_id = $DB->insert_record('inlider_report_challenge_main', array('courseid'=>$data->id));

  $modules = $DB->get_records('course_modules',array("course"=> $data->id));

  $k = 0;
  foreach($modules as $m){
    if($k != 0 || ($m->module != 9 && $k == 0)){ //Ignore News Forum
      $DB->insert_record('inlider_report_challenge_modules', array('main_id'=>$main_id, 'module_id'=>$m->id));
    }
    $k++;
  }


  foreach($data->courses as $c){
    if($c != $data->id){
      $DB->insert_record('inlider_report_challenge_related', array('courseid'=>$c,'main_id'=>$main_id));
    }
  }

  $returnurl = new moodle_url('/blocks/inlider_report_challenge/admin.php');
  redirect($returnurl);
}


$PAGE->set_url($main_url);


$title = 'Creación Curso Padre - Hijo';
$PAGE->set_title($title);
print $OUTPUT->header();
    print html_writer::tag('link','',array('href'=>$CFG->wwwroot.'/blocks/inlider_report_challenge/assets/css/select2.min.css','rel'=>'stylesheet'));
    $mform->display();
    print html_writer::tag('script','',array('src'=>$CFG->wwwroot.'/blocks/inlider_report_challenge/assets/js/custom.js'));
    print html_writer::tag('script','',array('src'=>$CFG->wwwroot.'/blocks/inlider_report_challenge/assets/js/select2.min.js'));
print $OUTPUT->footer();

