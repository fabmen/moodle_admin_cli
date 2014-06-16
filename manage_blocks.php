<?php

    // Allows the admin to configure blocks (hide/show, delete and configure)

define('CLI_SCRIPT', true);

    require(dirname(dirname(dirname(__FILE__))).'/config.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->libdir.'/tablelib.php');
	require_once($CFG->libdir.'/clilib.php');      // cli only functions

	list($options) = cli_get_params(array('name'=>'', 'show'=>false, 'hide'=>false, 'delete'=>false,'protect'=>false,'unprotect'=>false));
	
/// If data submitted, then process and store.

    if (!empty($options['hide'])) {
		echo "hiding ".$options['name']."...";
        if (!$block = $DB->get_record('block', array('name'=>$options['name']))) {
            echo 'block does not exist';
        }
        $DB->set_field('block', 'visible', '0', array('id'=>$block->id));      // Hide block
    }
    
    if (!empty($options['show'])) {
	echo "showing ".$options['name']."...";
        if (!$block = $DB->get_record('block', array('name'=>$options['name']))) {
            echo 'block does not exist';
        }
        $DB->set_field('block', 'visible', '1', array('id'=>$block->id));      // Show block
    }

    if (!isset($CFG->undeletableblocktypes) || (!is_array($CFG->undeletableblocktypes) && !is_string($CFG->undeletableblocktypes))) {
        $undeletableblocktypes = array('navigation', 'settings');
    } 
	else if (is_string($CFG->undeletableblocktypes)) {
        $undeletableblocktypes = explode(',', $CFG->undeletableblocktypes);
    } 
	else {
        $undeletableblocktypes = $CFG->undeletableblocktypes;
    }

    if (!empty($options['protect'])) {
	 echo "protecting ".$options['name']."...";
       if (!$block = $DB->get_record('block', array('name'=>$options['name']))) {
            echo 'block does not exist';
        }
        if (!in_array($block->name, $undeletableblocktypes)) {
           $undeletableblocktypes[] = $block->name;
            set_config('undeletableblocktypes', implode(',', $undeletableblocktypes));
        }
    }

     if (!empty($options['unprotect'])) {
       echo "unprotecting ".$options['name']."...";
        if (!$block = $DB->get_record('block', array('name'=>$options['name']))) {
            echo 'block does not exist';
        }
        if (in_array($block->name, $undeletableblocktypes)) {
            $undeletableblocktypes = array_diff($undeletableblocktypes, array($block->name));
            set_config('undeletableblocktypes', implode(',', $undeletableblocktypes));
        }
    }

    if (!empty($options['delete'])) {
		echo "deleting ".$options['name']."...";
        if (!$block = $DB->get_record('block', array('name'=>$options['name']))) {
            echo 'block does not exist';
        }
		if (get_string_manager()->string_exists('pluginname', "block_".$options['name'])) {
            $strblockname = get_string('pluginname', "block_".$options['name']);
        } else {
            $strblockname = $options['name'];
        }

            uninstall_plugin('block', $block->name);
			echo "sucssessfuly removed ".$strblockname;
    }