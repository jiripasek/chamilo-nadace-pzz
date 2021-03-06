<?php
/* For licensing terms, see /license.txt */
/**
 * Script
 * @package chamilo.gradebook
 */
/**
 * Init
 */
$language_file = 'gradebook';
require_once '../inc/global.inc.php';
$_in_course = true;
$course_code = api_get_course_id();
if ( empty ($course_code ) ) {
	$_in_course = false;
}

require_once 'lib/be.inc.php';
require_once 'lib/gradebook_functions.inc.php';
require_once 'lib/fe/catform.class.php';
api_block_anonymous_users();
block_students();
$get_select_cat=Security::remove_XSS($_GET['selectcat']);
$catadd = new Category();
$my_user_id = api_get_user_id();
$catadd->set_user_id($my_user_id);
$catadd->set_parent_id(Database::escape_string($get_select_cat));
$catcourse = Category :: load ($get_select_cat);
if ($_in_course) {
	$catadd->set_course_code($course_code);
} else {
	$catadd->set_course_code($catcourse[0]->get_course_code());
}
$form = new CatForm(CatForm :: TYPE_ADD, $catadd, 'add_cat_form', null, api_get_self() . '?selectcat='.$get_select_cat);
if ($form->validate()) {
	$values = $form->exportValues();
	$select_course=isset($values['select_course']) ? $values['select_course'] : array();
	$cat = new Category();
	if ($values['hid_parent_id'] == '0') {
		if ($select_course == 'COURSEINDEPENDENT') {
			$cat->set_name($values['name']);
			$cat->set_course_code(null);
		} else {
			$cat->set_course_code($select_course);
			$cat->set_name($values['name']);
		}
	} else {
		$cat->set_name($values['name']);
		$cat->set_course_code($values['course_code']);//?
	}
	$cat->set_description($values['description']);
	$cat->set_user_id($values['hid_user_id']);
	$cat->set_parent_id($values['hid_parent_id']);
	$cat->set_weight($values['weight']);
	if (empty ($values['visible'])) {
		$visible = 0;
	} else {
		$visible = 1;
	}
	$cat->set_visible($visible);
	$cat->add();
	header('Location: '.Security::remove_XSS($_SESSION['gradebook_dest']).'?addcat=&selectcat=' . $cat->get_parent_id());
	exit;
}

if ( !$_in_course ) {
	$interbreadcrumb[] = array ('url' => Security::remove_XSS($_SESSION['gradebook_dest']).'?selectcat='.$get_select_cat,'name' => get_lang('Gradebook'));
}
Display :: display_header(get_lang('NewCategory'));
$form->display();
Display :: display_footer();
