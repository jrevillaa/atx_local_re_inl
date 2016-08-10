<?php

class block_inlider_report_challenge extends block_base {

  function init() {
    $this->title = get_string('pluginname', 'block_inlider_report_challenge');
  }

  function has_config() {
      return true;
  }

  function get_content() {
    global $OUTPUT, $USER , $DB , $COURSE;
      
    $courseid = $this->page->course->id;
    $userid = $USER->id;

    if ($this->content !== null) {
      return $this->content;
    }

    if (empty($this->instance)) {
      $this->content = '';
      return $this->content;
    }

    $this->content = new stdClass();
    $this->content->text = ''; //translate this

    /*$cContext = context_system::instance(); 


    $admins = get_admins();
    

    $courses = $DB->get_records('inlider_report_challenge_main',array(),null,'courseid,id');
    $main = $DB->get_records('inlider_report_challenge_main');
    $childs = $DB->get_records('inlider_report_challenge_related',array(),null,'courseid,main_id');

    if(in_array($courseid,array_keys($courses))){
      $c = get_user_roles($cContext,$USER->id);
      $roles = array();
      foreach ($c as $k){
        $roles[] = $k->shortname;
      }
       if(in_array('manager',$roles) || in_array($USER->id,array_keys($admins))){
        
           $id = $DB->get_record('inlider_report_challenge_main',array('courseid' => $courseid));
          //print_r($id);
          //exit;
           $chijos = $DB->get_records('inlider_report_challenge_related',array('main_id' => $id->id));
          
         
           $this->content->text = '<div>Se encuentra en un curso Padre</div>'.'<div>A continuación se muestra sus cursos hijos:</div>';

           $url = new moodle_url('/blocks/inlider_report_challenge/inlider_report_challenge.php', array('id' =>$courseid));
          //$url1 = new moodle_url('', );
           $text = 'Actualizar Cursos Hijos'; //Translate this
           $this->content->text .= html_writer::link($url,$text,array('class'=>'btn btn-default'));
           $text = 'Mostrar Cursos Hijos';
           //$this->content->text .= html_writer::link($url1,$text,array('class'=>'btn btn-default'));
          
          $this->content->text .= self::generate_curse($chijos);

       }
      
    }elseif(in_array($courseid,array_keys($childs))){
      $this->content->text = '<div>Este es un curso hijo</div>';
    }else{

    }*/
    $url = new moodle_url('/blocks/inlider_report_challenge/inlider_report_challenge.php', array('courseid' =>$courseid, 'userid' => $userid));

    $text = 'Generar Reporte de Curso Challenge'; //Translate this
    //$this->content->text = '<div>Este es un curso hijo</div>';
    $this->content->text .= html_writer::link($url,$text,array('class'=>'btn btn-default' , 'target' => '_blank'));


  }

  function applicable_formats() {
      return array('course' => true);
  }


function generate_curse($data) {
    global $USER, $OUTPUT , $DB;
    $i = 1;
    if (empty($data)) {
        return 'No tiene cursos Hijos';
    }

    $table = new html_table();
    
    $table->head = array(
                        'Nro',
                        'Nombre Curso',
                        'Nombre Corto'
                    );
 
    foreach ($data as $k) {
       
       
      $courseh = $DB->get_record("course", array("id" => $k->courseid), '*', MUST_EXIST);
        $row = new html_table_row();


        $row->cells = array(
                        $i,
                        $courseh->fullname,
                        $courseh->shortname
                    );
        $table->data[] = $row;
        $i = $i +1;
    }

    return html_writer::table($table);
}
}



?>
