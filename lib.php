<?php

function inlider_report_challenge_main_modules($courseid = 0, $main_id){
  global $DB;

  if($courseid == 0){
    return FALSE;
  }

  $modules = $DB->get_records('course_modules',array("course"=> $courseid));
  $already = $DB->get_records('inlider_report_challenge_modules',array("main_id"=>$main_id),null,'module_id,main_id');
  $k = 0;
  foreach ($modules as $m) {
    if($k != 0 || ($m->module != 9 && $k == 0)){ //Ignore News Forum
      if(!in_array($m->id, array_keys($already))){
        $DB->insert_record('inlider_report_challenge_modules', array('main_id'=>$main_id, 'module_id'=>$m->id));
      }
    }
    $k++;
  }
}

function inlider_report_challenge_check_status($module,$courseid){
  global $DB;
  $object  = false;
  $exists = $DB->get_record('inlider_report_challenge_modules_course',array('smodule_id'=>$module->id,'course_id'=>$courseid));
  $list = $DB->get_records_menu('modules',array(),null,'id,name');
  $entity = $DB->get_record('course_modules',array('id'=>$module->module_id));

  if($entity){
    $instance = $DB->get_record($list[$entity->module],array('id'=>$entity->instance));
  }

  if(!$exists){
    if($entity){
      $object = new stdClass();
      $object->message = html_writer::tag('p','Crear actividad: '. $instance->name);
      $object->type = 1;
      $object->module = $entity;
      $object->instance = $instance;
    }
  }else{
    $course_entity = $DB->get_record('course_modules',array('id'=>$exists->module_id));
    if($course_entity){
      $course_instance = $DB->get_record($list[$course_entity->module],array('id'=>$course_entity->instance));
    }

    //If the module still there
    if(isset($instance)){

      if(isset($course_instance)){

        if($instance->timemodified > $course_instance->timemodified || $entity->visible != $course_entity->visible){
          $object = new stdClass();
          $object->message = html_writer::tag('p','La actividad: '. $instance->name.' debe ser actualizada');
          $object->type = 2;
          $object->module = $entity;
          $object->instance = $instance;
        }

      }else{
        $object = new stdClass();
        $object->message = html_writer::tag('p','Crear actividad: '. $instance->name);
        $object->type = 1;
        $object->module = $entity;
        $object->instance = $instance;
      }

    }else{
      if(isset($course_instance)){
        $object = new stdClass();
        $object->message = html_writer::tag('p','La actividad: '. $course_instance->name.' debe ser eliminada');
        $object->type = 3;
        $object->module = $entity;
      }
    }

  }

  return $object;

}



function inlider_report_challenge_create_module($object,$module,$courseid){
    global $DB;

    $newcmid = inlider_report_challenge_restore_module($object->module->id,$courseid);

    $exists =  $DB->get_record('inlider_report_challenge_modules_course',array('smodule_id'=>$module->id,'course_id'=>$courseid));

    if($exists){
      $exists->module_id = $newcmid;
      $DB->update_record('inlider_report_challenge_modules_course',$exists);
    }else{

      $cmodule = new stdClass();
      $cmodule->module_id = $newcmid;
      $cmodule->smodule_id = $module->id;
      $cmodule->course_id = $courseid;
      $DB->insert_record('inlider_report_challenge_modules_course',$cmodule);
    }

    if($object->module->module == 17){
      $context = context_module::instance($object->module->id);
      $files = $DB->get_records('files',array('contextid'=>$context->id));
      $file = array_shift($files);

      $new_context = context_module::instance($newcmid);

      $newfiles = $DB->get_records('files',array('contextid'=>$new_context->id));

      foreach($newfiles as $nfile){
        $nfile->userid = $file->userid;
        $DB->update_record('files',$nfile);
      }
    }

    print html_writer::tag('p','Actividad '.$object->instance->name.' creada');
}



function inlider_report_challenge_update_module($object,$module,$courseid){
  global $DB;

  $list = $DB->get_records_menu('modules',array(),null,'id,name');

  $course_module =  $DB->get_record('inlider_report_challenge_modules_course',array('smodule_id'=>$module->id,'course_id'=>$courseid));
  $course_entity = $DB->get_record('course_modules',array('id'=>$course_module->module_id));
  $course_instance = $DB->get_record($list[$course_entity->module],array('id'=>$course_entity->instance));
  $update = TRUE;
  switch ($list[$object->module->module]) {
    case 'resource':
      course_delete_module($course_module->module_id);
      $newcmid = inlider_report_challenge_restore_module($object->module->id,$courseid);
      $context = context_module::instance($object->module->id);
      $files = $DB->get_records('files',array('contextid'=>$context->id));
      $file = array_shift($files);
      $new_context = context_module::instance($newcmid);

      $newfiles = $DB->get_records('files',array('contextid'=>$new_context->id));

      foreach($newfiles as $nfile){
        $nfile->userid = $file->userid;
        $DB->update_record('files',$nfile);
      }
      $exists =  $DB->get_record('inlider_report_challenge_modules_course',array('smodule_id'=>$module->id,'course_id'=>$courseid));
      $exists->module_id = $newcmid;
      $DB->update_record('inlider_report_challenge_modules_course',$exists);
      break;
    case 'quiz':
      $attempts = $DB->get_records('quiz_attempts',array('quiz'=>$course_instance->id));
      if(!empty($attempts)){
        $update = FALSE;
        print html_writer::tag('p','Actividad '.$course_instance->name.' no se ha actualizado porque ya tiene intentos.');
      }else{
        course_delete_module($course_module->module_id);
        $newcmid = inlider_report_challenge_restore_module($object->module->id,$courseid);
        $exists =  $DB->get_record('inlider_report_challenge_modules_course',array('smodule_id'=>$module->id,'course_id'=>$courseid));
        $exists->module_id = $newcmid;
        $DB->update_record('inlider_report_challenge_modules_course',$exists);
      }
      break;
    case 'forum':
      $attempts = $DB->get_records('forum_discussions',array('forum'=>$course_instance->id));
      if(!empty($attempts)){
        $update = FALSE;
        print html_writer::tag('p','Actividad '.$course_instance->name.' no se ha actualizado porque ya tiene intentos.');
      }else{
        course_delete_module($course_module->module_id);
        $newcmid = inlider_report_challenge_restore_module($object->module->id,$courseid);
        $exists =  $DB->get_record('inlider_report_challenge_modules_course',array('smodule_id'=>$module->id,'course_id'=>$courseid));
        $exists->module_id = $newcmid;
        $DB->update_record('inlider_report_challenge_modules_course',$exists);
      }
        case 'assignment':
      $attempts = $DB->get_records('assign_submission',array('assignment'=>$course_instance->id));
      if(!empty($attempts)){
        $update = FALSE;
        print html_writer::tag('p','Actividad '.$course_instance->name.' no se ha actualizado porque ya tiene intentos.');
      }else{
        course_delete_module($course_module->module_id);
        $newcmid = inlider_report_challenge_restore_module($object->module->id,$courseid);
        $exists =  $DB->get_record('inlider_report_challenge_modules_course',array('smodule_id'=>$module->id,'course_id'=>$courseid));
        $exists->module_id = $newcmid;
        $DB->update_record('inlider_report_challenge_modules_course',$exists);
      }
      break;
    default:
      $instance = (array)$object->instance;

      foreach($instance as $key => $value){
        if(in_array($key,array('id','course','timecreated','timemodified'))) continue;
        $course_instance->$key = $value;
      }

      $course_instance->timemodified = time();
      $DB->update_record($list[$object->module->module],$course_instance);
      break;
  }

  //reloading from DB
  $course_module =  $DB->get_record('inlider_report_challenge_modules_course',array('smodule_id'=>$module->id,'course_id'=>$courseid));
  $course_entity = $DB->get_record('course_modules',array('id'=>$course_module->module_id));
  $course_instance = $DB->get_record($list[$course_entity->module],array('id'=>$course_entity->instance));


  $m = (array)$object->module;

  foreach($m as $field => $value){
    if(in_array($field,array('visible','visibleold','indent','showdescription','completionview','completion'))){
      $course_entity->$field = $value;
    }
  }



  $DB->update_record('course_modules',$course_entity);

  if($update){
    print html_writer::tag('p','Actividad '.$object->instance->name.' actualizada');
  }


}

function inlider_report_challenge_restore_module($moduleid,$courseid){
    global $CFG, $DB, $USER;
    require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
    require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
    require_once($CFG->libdir . '/filelib.php');
    $cm = get_coursemodule_from_id(null,$moduleid);

    $a          = new stdClass();
    $a->modtype = get_string('modulename', $cm->modname);
    $a->modname = format_string($cm->name);

    if (!plugin_supports('mod', $cm->modname, FEATURE_BACKUP_MOODLE2)) {
        throw new moodle_exception('duplicatenosupport', 'error', '', $a);
    }

    // Backup the activity.

    $bc = new backup_controller(backup::TYPE_1ACTIVITY, $cm->id, backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO, backup::MODE_IMPORT, $USER->id);

    $backupid       = $bc->get_backupid();
    $backupbasepath = $bc->get_plan()->get_basepath();

    $bc->execute_plan();

    $bc->destroy();

    // Restore the backup immediately.

    $rc = new restore_controller($backupid, $courseid,
            backup::INTERACTIVE_NO, backup::MODE_IMPORT, $USER->id, backup::TARGET_CURRENT_ADDING);

    $cmcontext = context_module::instance($cm->id);
    if (!$rc->execute_precheck()) {
        $precheckresults = $rc->get_precheck_results();
        if (is_array($precheckresults) && !empty($precheckresults['errors'])) {
            if (empty($CFG->keeptempdirectoriesonbackup)) {
                fulldelete($backupbasepath);
            }
        }
    }

    $rc->execute_plan();

    $newcmid = null;
    $tasks = $rc->get_plan()->get_tasks();
    foreach ($tasks as $task) {
        if (is_subclass_of($task, 'restore_activity_task')) {
            if ($task->get_old_contextid() == $cmcontext->id) {
                $newcmid = $task->get_moduleid();
                break;
            }
        }
    }

    return $newcmid;
}

function inlider_report_challenge_delete_module($object,$module,$courseid){
  global $DB;

  $course_module =  $DB->get_record('inlider_report_challenge_modules_course',array('smodule_id'=>$module->id,'course_id'=>$courseid));

  $list = $DB->get_records_menu('modules',array(),null,'id,name');
  $entity = $DB->get_record('course_modules',array('id'=>$course_module->module_id));
  $instance = $DB->get_record($list[$entity->module],array('id'=>$entity->instance));
  $delete = TRUE;
  switch ($list[$object->module->module]) {
    case 'quiz':
      $attempts = $DB->get_records('quiz_attempts',array('quiz'=>$instance->id));
      if(!empty($attempts)){
        $delete = FALSE;
        print html_writer::tag('p','Actividad '.$instance->name.' no se ha eliminado porque ya tiene intentos.');
      }
      break;
    case 'assignment':
      $attempts = $DB->get_records('assignment_submissions',array('assignment'=>$instance->id));
      if(!empty($attempts)){
        $delete = FALSE;
        print html_writer::tag('p','Actividad '.$instance->name.' no se ha eliminado porque ya tiene intentos.');
      }
      break;
    case  'forum':
      $attempts = $DB->get_records('forum_discussions',array('forum'=>$instance->id));
      if(!empty($attempts)){
        $delete = FALSE;
        print html_writer::tag('p','Actividad '.$instance->name.' no se ha eliminado porque ya tiene intentos.');
      }
      break;
    default:
      break;
  }

  if($delete){
    course_delete_module($course_module->module_id);
    print html_writer::tag('p','Actividad '.$instance->name.' eliminada');
  }


}


function inlider_report_challenge_get_sections($courseid){
  global $DB;
  $sections = $DB->get_records('course_sections',array('course'=>$courseid));
  $final_sections = array();

  foreach ($sections as $s) {
    $final_sections[$s->section] = $s;
  }

  return $final_sections;

}












