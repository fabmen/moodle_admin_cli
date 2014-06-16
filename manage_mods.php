<?php
      // Allows the admin to manage activity modules
define('CLI_SCRIPT', true);

    require(dirname(dirname(dirname(__FILE__))).'/config.php');
	require(dirname(dirname(dirname(__FILE__))).'/course/lib.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->libdir.'/tablelib.php');
	require_once($CFG->libdir.'/clilib.php');      // cli only functions

    // defines
    define('MODULE_TABLE','module_administration_table');

list($options) = cli_get_params(array('name'=>'', 'show'=>false, 'hide'=>false, 'delete'=>false));

    if (!empty($options['hide'])) {
	echo "hiding ".$options['name']."...";
        if (!$module = $DB->get_record("modules", array("name"=>$options['name']))) {
            print_error('moduledoesnotexist', 'error');
        }
        $DB->set_field("modules", "visible", "0", array("id"=>$module->id)); // Hide main module
        // Remember the visibility status in visibleold
        // and hide...
        $sql = "UPDATE {course_modules}
                   SET visibleold=visible, visible=0
                 WHERE module=?";
        $DB->execute($sql, array($module->id));
        // clear the course modinfo cache for courses
        // where we just deleted something
        $sql = "UPDATE {course}
                   SET modinfo=''
                 WHERE id IN (SELECT DISTINCT course
                                FROM {course_modules}
                               WHERE visibleold=1 AND module=?)";
        $DB->execute($sql, array($module->id));
    }

    if (!empty($options['show'])) {
	echo "showing ".$options['name']."...";
        if (!$module = $DB->get_record("modules", array("name"=>$options['name']))) {
           echo 'moduledoesnotexist';
        }
        $DB->set_field("modules", "visible", "1", array("id"=>$module->id)); // Show main module
        $DB->set_field('course_modules', 'visible', '1', array('visibleold'=>1, 'module'=>$module->id)); // Get the previous saved visible state for the course module.
        // clear the course modinfo cache for courses
        // where we just made something visible
        $sql = "UPDATE {course}
                   SET modinfo = ''
                 WHERE id IN (SELECT DISTINCT course
                                FROM {course_modules}
                               WHERE visible=1 AND module=?)";
        $DB->execute($sql, array($module->id));
    }

    if (!empty($options['delete'])) {
 echo "deleting ".$options['name']."...";
		if (get_string_manager()->string_exists('modulename', $options['name'])) {
            $strmodulename = get_string('modulename', $options['name']);
        } else {
            $strmodulename = $options['name'];
        }
		
 // Delete everything!!
            if ($options['name'] == "forum") {
                echo "cannotdeleteforummodule";
}
            uninstall_plugin('mod', $options['name']);
			echo "sucssessfuly removed ".$strmodulename;
            exit;
        }
