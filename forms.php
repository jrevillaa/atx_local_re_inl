<?php

defined('MOODLE_INTERNAL') || die();


require_once($CFG->libdir . '/formslib.php');


class inlider_report_challenge_create_main extends moodleform {

  function definition() {
    global $DB;
    $mform = & $this->_form;

    $course = $DB->get_records_menu('course',array(),null,'id,fullname');
    unset($course[1]);

    $main = $DB->get_records('inlider_report_challenge_main');
    foreach($main as $m){
      unset($course[$m->courseid]);
    
    }
    $childs = $DB->get_records('inlider_report_challenge_related');

    foreach($childs as $c){
      unset($course[$c->courseid]);
    }

    $mform->addElement('select', 'id', 'Curso Padre', $course,array('class'=>'select2'));
    $mform->setType('id', PARAM_INT);


    $select = $mform->addElement('select', 'courses', 'Cursos Hijos', $course,array('class'=>'select2'));

    $select->setMultiple(true);

    $mform->addRule('courses', 'Campo Requerido', 'required', null, 'client', false, false);
    $mform->addRule('id', 'Campo Requerido', 'required', null, 'client', false, false);

    $this->add_action_buttons();
  }
}



class inlider_report_challenge_edit_main extends moodleform {

  function definition() {
    global $DB;
    $mform = & $this->_form;
    $courseid = $this->_customdata['courseid'];
    $id = $this->_customdata['id'];
    $course = $DB->get_records_menu('course',array(),null,'id,fullname');
    unset($course[1]);


    $main = $DB->get_records('inlider_report_challenge_main');
    foreach($main as $m){
      if($m->courseid != $courseid){
        unset($course[$m->courseid]);
      }
    }
    $course_childs = $DB->get_records_menu('inlider_report_challenge_related',array('main_id'=>$id),null,'courseid,main_id');
    $childs = $DB->get_records('inlider_report_challenge_related');

    foreach($childs as $c){
      if(!in_array($c->courseid, array_keys($course_childs))) unset($course[$c->courseid]);
    }

    $mform->addElement('select', 'cid', 'Curso Padre', array($courseid => $course[$courseid]), array('disabled' => 'disabled'));
    $mform->setType('cid', PARAM_INT);

    unset($course[$courseid]);
    $select = $mform->addElement('select', 'courses', 'Cursos Hijos', $course,array('class'=>'select2') );
    $mform->setDefault('courses', array_keys($course_childs));
    $select->setMultiple(true);

    $mform->addRule('courses', 'Campo Requerido', 'required', null, 'client', false, false);
    $mform->addElement('html','Nota: Sólo se puede modificar los cursos hijos, si desea cambiar el curso padre deberá eliminar esta relación.');
    $this->add_action_buttons();
  }
}




// Form to select start and end date ranges and session time
class inlider_report_challenge_inlider_report_challenge extends moodleform {

  function definition() {
    global $DB;
    $mform = & $this->_form;

    $mform->addElement('advcheckbox', 'sections','Sobreescribir nombre de las secciones y formato', '', array('group' => 1), array(0, 1));

    $mform->addElement('advcheckbox', 'delete','Eliminar actividades en cursos hijos?', '', array('group' => 1), array(0, 1));

    $this->add_action_buttons('true','Sincronizar en Cursos Hijos');
  }

}

class inlider_report_challenge_delete_main extends moodleform {

  function definition() {
    global $DB;
    $mform = & $this->_form;

    $mform->addElement('html','Seguro que desear eliminar esta relacion?');
    $this->add_action_buttons('true','Eliminar');
  }

}

class inlider_report_challenge_clear_main extends moodleform {

  function definition() {
    global $DB;
    $mform = & $this->_form;

    $mform->addElement('html','Seguro que desear Eliminar todos los cursos hijo?');
    $this->add_action_buttons('true','Limpiar Hijos');
  }

}

