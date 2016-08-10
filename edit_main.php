<?php
global $DB, $PAGE, $OUTPUT;

require_once("../../config.php");
require_once($CFG->libdir.'/adminlib.php');
include('forms.php');
include('lib.php');

$id = required_param('id', PARAM_INT);

admin_externalpage_setup('blockinlider_report_challenge');

$context = context_system::instance();
require_login();
require_capability('block/inlider_report_challenge:config',$context);

$main_url = new moodle_url('/blocks/inlider_report_challenge/edit_main.php',array('id'=>$id));

$inlider_report_challenge = $DB->get_record('inlider_report_challenge_main',array('id'=>$id));

$mform = new inlider_report_challenge_edit_main($main_url,array('courseid'=>$inlider_report_challenge->courseid,'id'=>$id));

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
  $returnurl = new moodle_url('/blocks/inlider_report_challenge/admin.php');
  redirect($returnurl);
} else if ($data = $mform->get_data()) {


  $DB->delete_records('inlider_report_challenge_related', array('main_id'=>$id));


  if(is_array($data->courses)){
    foreach($data->courses as $c){
      $DB->insert_record('inlider_report_challenge_related', array('courseid'=>$c,'main_id'=>$id));
    }
  }

  $returnurl = new moodle_url('/blocks/inlider_report_challenge/admin.php');
  redirect($returnurl);
}


$PAGE->set_url($main_url);
$title = 'Edición Curso Padre - Hijo';
$PAGE->set_title($title);
print $OUTPUT->header();
    print html_writer::tag('link','',array('href'=>$CFG->wwwroot.'/blocks/inlider_report_challenge/assets/css/select2.min.css','rel'=>'stylesheet'));
    $mform->display();
    print html_writer::tag('script','',array('src'=>$CFG->wwwroot.'/blocks/inlider_report_challenge/assets/js/custom.js'));
    print html_writer::tag('script','',array('src'=>$CFG->wwwroot.'/blocks/inlider_report_challenge/assets/js/select2.min.js'));

print $OUTPUT->footer();

