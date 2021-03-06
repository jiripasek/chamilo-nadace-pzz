<?php
/* For licensing terms, see /license.txt */

/**
 * This script allows professors and administrative staff to create course sites.
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @author Roan Embrechts, refactoring
 * @package chamilo.create_course
 * "Course validation" feature:
 * @author Jose Manuel Abuin Mosquera <chema@cesga.es>, Centro de Supercomputacion de Galicia
 * "Course validation" feature, technical adaptation for Chamilo 1.8.8:
 * @author Ivan Tcholakov <ivantcholakov@gmail.com>
 */
/**
 * Code
 */

// Name of the language file that needs to be included.
$language_file = 'create_course';

// Flag forcing the "current course" reset.
$cidReset = true;

// Including the global initialization file.
require_once '../inc/global.inc.php';

// Section for the tabs.
$this_section = SECTION_COURSES;

// Include configuration file.
require_once api_get_path(CONFIGURATION_PATH).'add_course.conf.php';

// "Course validation" feature. This value affects the way of a new course creation:
// true  - the new course is requested only and it is created after approval;
// false - the new course is created immedialely, after filling this form.
$course_validation_feature = api_get_setting('course_validation') == 'true';

// Require additional libraries.
require_once api_get_path(LIBRARY_PATH).'add_course.lib.inc.php';
require_once api_get_path(LIBRARY_PATH).'fileManage.lib.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path(CONFIGURATION_PATH).'course_info.conf.php';
if ($course_validation_feature) {
    require_once api_get_path(LIBRARY_PATH).'course_request.lib.php';
    require_once api_get_path(LIBRARY_PATH).'mail.lib.inc.php';
}

$htmlHeadXtra[] = '<script type="text/javascript">
function setFocus(){
    $("#title").focus();
    }
    $(window).load(function () {
      setFocus();
    });
    
    
    function advanced_parameters() {
        if(document.getElementById(\'options\').style.display == \'none\') {
            document.getElementById(\'options\').style.display = \'block\';
            document.getElementById(\'img_plus_and_minus\').innerHTML=\'&nbsp;<img style="vertical-align:middle;" src="../img/div_hide.gif" alt="" />&nbsp;'.get_lang('AdvancedParameters').'\';
        } else {
            document.getElementById(\'options\').style.display = \'none\';
            document.getElementById(\'img_plus_and_minus\').innerHTML=\'&nbsp;<img style="vertical-align:middle;" src="../img/div_show.gif" alt="" />&nbsp;'.get_lang('AdvancedParameters').'\';
        }
    }
    
    
</script>';

$interbreadcrumb[] = array('url' => api_get_path(WEB_PATH).'user_portal.php', 'name' => get_lang('MyCourses'));

// Displaying the header.
$tool_name = $course_validation_feature ? get_lang('CreateCourseRequest') : get_lang('CreateSite');

if (api_get_setting('allow_users_to_create_courses') == 'false' && !api_is_platform_admin()) {
    api_not_allowed(true);
}
Display :: display_header($tool_name);

// Check access rights.
if (!api_is_allowed_to_create_course()) {
    Display :: display_error_message(get_lang('NotAllowed'));
    Display::display_footer();
    exit;
}

global $_configuration;
$dbnamelength = strlen($_configuration['db_prefix']);
// Ensure the database prefix + database name do not get over 40 characters.
$maxlength = 40 - $dbnamelength;

// Build the form.
$form = new FormValidator('add_course');

// Form title
$form->addElement('header', '', $tool_name);

// Title
$form->addElement('text', 'title', get_lang('CourseName'), array('size' => '60', 'id' => 'title'));
$form->applyFilter('title', 'html_filter');
$form->addElement('static', null, null, get_lang('Ex'));
$form->addRule('title', get_lang('ThisFieldIsRequired'), 'required');

// Course category.
$categories_select = $form->addElement('select', 'category_code', get_lang('Fac'), array(), array('id'=> 'category_code','class'=>'chzn-select', 'style'=>'width:350px'));
$form->applyFilter('category_code', 'html_filter');
CourseManager::select_and_sort_categories($categories_select);
$form->addElement('static', null, null, get_lang('TargetFac'));


$form -> addElement('html','<div class="row">
    <div class="label">&nbsp;</div>
    <div class="formw">
        <a href="javascript://" onclick=" return advanced_parameters()"><span id="img_plus_and_minus"><div style="vertical-align:top;" ><img style="vertical-align:middle;" src="../img/div_show.gif" alt="" />&nbsp;'.get_lang('AdvancedParameters').'</div></span></a>
    </div>
    </div>');

$form -> addElement('html','<div id="options" style="display:none">');

// Course code.
$form->add_textfield('wanted_code', get_lang('Code'), '', array('size' => $maxlength, 'maxlength' => $maxlength));
$form->addElement('static', null, null, get_lang('OnlyLettersAndNumbers'));
$form->applyFilter('wanted_code', 'html_filter');
$form->addRule('wanted_code', get_lang('Max'), 'maxlength', $maxlength);

/*if ($course_validation_feature) {
    $form->addRule('wanted_code', get_lang('ThisFieldIsRequired'), 'required');
}*/

// The teacher.
$titular = & $form->add_textfield('tutor_name', get_lang('Professor'), null, array('size' => '60', 'disabled' => 'disabled'));
$form->addElement('static', null, null, get_lang('ExplicationTrainers'));
//$form->applyFilter('tutor_name', 'html_filter');

if ($course_validation_feature) {

    // Description of the requested course.
    $form->addElement('textarea', 'description', get_lang('Description'), array('style' => 'border:#A5ACB2 solid 1px; font-family:arial,verdana,helvetica,sans-serif; font-size:12px', 'rows' => '3', 'cols' => '116'));
    //$form->addRule('description', get_lang('ThisFieldIsRequired'), 'required');

    // Objectives of the requested course.
    $form->addElement('textarea', 'objetives', get_lang('Objectives'), array('style' => 'border:#A5ACB2 solid 1px; font-family:arial,verdana,helvetica,sans-serif; font-size:12px', 'rows' => '3', 'cols' => '116'));
    //$form->addRule('objetives', get_lang('ThisFieldIsRequired'), 'required');

    // Target audience of the requested course.
    $form->addElement('textarea', 'target_audience', get_lang('TargetAudience'), array('style' => 'border:#A5ACB2 solid 1px; font-family:arial,verdana,helvetica,sans-serif; font-size:12px', 'rows' => '3', 'cols' => '116'));
    //$form->addRule('target_audience', get_lang('ThisFieldIsRequired'), 'required');
}

// Course language.
$form->addElement('select_language', 'course_language', get_lang('Ln'));
$form->applyFilter('select_language', 'html_filter');

// Exemplary content checkbox.
$form->addElement('checkbox', 'exemplary_content', get_lang('FillWithExemplaryContent'));



if ($course_validation_feature) {

    // A special URL to terms and conditions that is set in the platform settings page.
    $terms_and_conditions_url = trim(api_get_setting('course_validation_terms_and_conditions_url'));

    // If the special setting is empty, then we may get the URL from Chamilo's module "Terms and conditions", if it is activated.
    if (empty($terms_and_conditions_url)) {
        if (api_get_setting('allow_terms_conditions') == 'true') {
            $terms_and_conditions_url = api_get_path(WEB_CODE_PATH).'auth/inscription.php?legal';
        }
    }

    if (!empty($terms_and_conditions_url)) {
        // Terms and conditions to be accepted before sending a course request.
        $form->addElement('checkbox', 'legal', get_lang('IAcceptTermsAndConditions'), '', 1);
        $form->addRule('legal', get_lang('YouHaveToAcceptTermsAndConditions'), 'required');
        // Link to terms and conditions.
        $link_terms_and_conditions = '<script type="text/JavaScript">
        <!--
        function MM_openBrWindow(theURL,winName,features) { //v2.0
            window.open(theURL,winName,features);
        }
        //-->
        </script>
        <div class="row">
        <div class="formw">
        <a href="#" onclick="javascript: MM_openBrWindow(\''.$terms_and_conditions_url.'\',\'Conditions\',\'scrollbars=yes, width=800\')">';
        $link_terms_and_conditions .= get_lang('ReadTermsAndConditions').'</a></div></div>';
        $form->addElement('html', $link_terms_and_conditions);
    }
}

$form -> addElement('html','</div>');

// Submit button.
$form->addElement('style_submit_button', null, $course_validation_feature ? get_lang('CreateThisCourseRequest') : get_lang('CreateCourseArea'), 'class="add"');

// The progress bar of this form.
$form->add_progress_bar();

// Set default values.
if (isset($_user['language']) && $_user['language'] != '') {
    $values['course_language'] = $_user['language'];
} else {
    $values['course_language'] = api_get_setting('platformLanguage');
}
$values['tutor_name'] = api_get_person_name($_user['firstName'], $_user['lastName'], null, null, $values['course_language']);
$form->setDefaults($values);

// Validate the form.
if ($form->validate()) {
    $course_values = $form->exportValues();

    $wanted_code = trim(Security::remove_XSS(stripslashes($course_values['wanted_code'])));
    $tutor_name = stripslashes($course_values['tutor_name']);
    $category_code = $course_values['category_code'];
    $title = Security::remove_XSS(stripslashes($course_values['title']));
    $course_language = $course_values['course_language'];
    $exemplary_content = !empty($course_values['exemplary_content']);

    if ($course_validation_feature) {
        $description = Security::remove_XSS(stripslashes($course_values['description']));
        $objetives = Security::remove_XSS(stripslashes($course_values['objetives']));
        $target_audience = Security::remove_XSS(stripslashes($course_values['target_audience']));
        $status = '0';
    }

    if ($wanted_code == '') {
        $wanted_code = generate_course_code(api_substr($title, 0, $maxlength));
    }

    // Check whether the requested course code has already been occupied.
    if (!$course_validation_feature) {
        $course_code_ok = !CourseManager::course_code_exists($wanted_code);
    } else {
        $course_code_ok = !CourseRequestManager::course_code_exists($wanted_code);
    }

    if ($course_code_ok) {

        if (!$course_validation_feature) {

            // Create the course immediately.

            $keys = define_course_keys($wanted_code, '', $_configuration['db_prefix']);

            if (count($keys)) {

                $visual_code = $keys['currentCourseCode'];
                $code = $keys['currentCourseId'];
                $db_name = $keys['currentCourseDbName'];
                $directory = $keys['currentCourseRepository'];

                $expiration_date = time() + $firstExpirationDelay;
                prepare_course_repository($directory, $code);
                update_Db_course($db_name);
                $pictures_array = fill_course_repository($directory,  $exemplary_content);
                fill_Db_course($db_name, $directory, $course_language, $pictures_array, $exemplary_content);
                register_course($code, $visual_code, $directory, $db_name, $tutor_name, $category_code, $title, $course_language, api_get_user_id(), $expiration_date);

                // Preparing a confirmation message.
                $link = api_get_path(WEB_COURSE_PATH).$directory.'/';
                $message = get_lang('JustCreated');
                $message .= ' <a href="'.$link.'">'.$title.'</a>';

                Display :: display_confirmation_message($message, false);
                echo '<div style="float: right; margin:0px; padding: 0px;">' .
                    '<a class="bottom-link" href="'.api_get_path(WEB_PATH).'user_portal.php">'.get_lang('Enter').'</a>' .
                    '</div>';

            } else {

                Display :: display_error_message(get_lang('CourseCreationFailed'), false);
                // Display the form.
                $form->display();

            }

        } else {

            // Create a request for a new course.

            $request_id = CourseRequestManager::create_course_request($wanted_code, $title, $description, $category_code, $course_language, $objetives, $target_audience, api_get_user_id(), $exemplary_content);

            if ($request_id) {

                $course_request_info = CourseRequestManager::get_course_request_info($request_id);
                $message = (is_array($course_request_info) ? '<strong>'.$course_request_info['code'].'</strong> : ' : '').get_lang('CourseRequestCreated');
                Display :: display_confirmation_message($message, false);
                echo '<div style="float: right; margin:0px; padding: 0px;">' .
                    '<a class="bottom-link" href="'.api_get_path(WEB_PATH).'user_portal.php">'.get_lang('Enter').'</a>' .
                    '</div>';

            } else {

                Display :: display_error_message(get_lang('CourseRequestCreationFailed'), false);
                // Display the form.
                $form->display();

            }
        }

    } else {
        Display :: display_error_message(get_lang('CourseCodeAlreadyExists'), false);
        // Display the form.
        $form->display();
        //echo '<p>'.get_lang('CourseCodeAlreadyExistExplained').'</p>';
    }

} else {
    if (!$course_validation_feature) {
        Display::display_normal_message(get_lang('Explanation'));
    }    
    // Display the form.
    $form->display();    
}

// Footer
Display :: display_footer();
