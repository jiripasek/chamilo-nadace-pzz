<?php
/* For licensing terms, see /license.txt */

/**
 *	This script displays the footer that is below (almost)
 *	every Chamilo web page.
 *
 *	@package chamilo.include
 */

// Display of tool_navigation_menu according to admin setting.
if (api_get_setting('show_navigation_menu') != 'false') {
   $course_id = api_get_course_id();
   if (!empty($course_id) && ($course_id != -1)) {
   		if (api_get_setting('show_navigation_menu') != 'icons') {
	    	echo '</div> <!-- end #center -->';
    		echo '</div> <!-- end #centerwrap -->';
		}
      	require_once api_get_path(INCLUDE_PATH).'tool_navigation_menu.inc.php';
      	show_navigation_menu();
   }
}

?>
<div class="clear">&nbsp;</div> <!-- 'clearing' div to make sure that footer stays below the main and right column sections -->
</div> <!-- end of #main" started at the end of banner.inc.php -->
</div> <!-- end of #main" started at the end of banner.inc.php -->

<div class="push"></div>
</div> <!-- end of #wrapper section -->

<div id="footer"> <!-- start of #footer section -->
<div id="bottom_corner"></div>
<?php
global $_configuration;

echo '<div class="copyright">';
echo "<span style=\"font-size: 150%;\"> V případě potřeby kontaktujte admina přes <a target=\"_blank\" href=\"http://www.nadacepzz.cz/cz/kontaktovat_admina\" title=\"Formulář ke kontaktování admina\">tento formulář</a>, případně i <a href=\"mailto:sandrikova@nadacepzz.cz\">sandrikova@nadacepzz.cz</a> nebo 731 169 825.</span>"; //SoftGate
	if (api_get_setting('show_administrator_data') == 'true') {
		// Platform manager
		//SG
		//echo '<div align="right">', get_lang('Manager'), ' : ', Display::encrypted_mailto_link(api_get_setting('emailAdministrator'), api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname'))).'</div>';
	}
	//SG
	//echo '<div align="right">'.get_lang('Platform').' <a href="', $_configuration['software_url'], '" target="_blank">', $_configuration['software_name'], ' ', $_configuration['system_version'], '</a>';
	echo '<div align="right">edupolis ', date('Y').' powered by <a href="http://chamilo.org">Chamilo</a>';
	echo '</div>';
echo '</div>'; //copyright div

echo '<div class="footer_emails">';

/*	Plugins for footer section */

echo '<div id="plugin-footer">';
api_plugin('footer');
echo '</div>';

if (api_get_setting('show_tutor_data') == 'true') {
	// Course manager
	$id_course = api_get_course_id();
	$id_session = api_get_session_id();
	if (isset($id_course) && $id_course != -1) {
		echo '<div id="platformmanager">';
		if ($id_session != 0){
			$coachs_email = CourseManager::get_email_of_tutor_to_session($id_session, $id_course);
			$email_link = array();
			foreach ($coachs_email as $coach_email) {
				foreach ($coach_email as $email => $username) {
					$email_link[] = Display::encrypted_mailto_link($email, $username);
				}
			}
			if (count($coachs_email) > 1) {
				$bar = '<br />';
				echo get_lang('Coachs').' : <ul>';
				echo '<li>'.implode("<li>", $email_link);
				echo '</ul>';
			} elseif (count($coachs_email) == 1) {
				echo get_lang('Coach').' : ';
				echo implode("&nbps;", $email_link);
			} elseif (count($coachs_email) == 0) {
				echo '';
			}
		}
		echo '</div>';
	}
	echo '<br>';
}

echo '<div style="clear:both"></div>';
$class = '';

if (api_get_setting('show_teacher_data') == 'true') {
	if (api_get_setting('show_tutor_data') == 'false') {
		$class = 'platformmanager';
	} else {
		$class = 'coursemanager';
	}
	// course manager
	$id_course = api_get_course_id();
	if (isset($id_course) && $id_course != -1) {
		echo '<div id="'.$class.'">';
		$mail = CourseManager::get_emails_of_tutors_to_course($id_course);
		if (!empty($mail)) {
			if (count($mail) > 1) {
				echo get_lang('Teachers').' : <ul>';
				foreach ($mail as $value => $key) {
					foreach ($key as $email => $name) {
						echo '<li>'.Display::encrypted_mailto_link($email, $name).'</li>';
					}
				}
				echo '</ul>';
			} else {
				echo get_lang('Teacher').' : ';
				foreach ($mail as $value => $key) {
					foreach ($key as $email => $name) {
						echo Display::encrypted_mailto_link($email, $name).'<br />';
					}
				}
			}
		}
		echo '</div>';
	}
}
echo '</div>';

echo '</div> <!-- end of #footer -->';

// Test server mode indicator and information for testing purposes.
if (api_is_platform_admin()) {
	if (api_get_setting('server_type') == 'test') {

		echo '<br /><a href="'.api_get_path(WEB_CODE_PATH).'admin/settings.php?category=Platform#server_type">';
		echo '<span style="background-color: white; color: red; border: 1px solid red;">&nbsp;'.get_lang('TestServerMode').'&nbsp;</span></a>';

		// @todo page execution time
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$starttime = $_SESSION['page_start_time_execution'];
		$totaltime = ($endtime - $starttime);

		$starttime = null;
		unset($_SESSION['page_start_time_execution']);

		$totaltime = number_format(($totaltime), 4, '.', '');
	    echo '<h2>'.get_lang('PageExecutionTimeWas').' '.$totaltime.' '.get_lang('Seconds').'</h2>';
	    unset($_SESSION['page_start_time_execution']);


	    // Memory usage
	    echo get_lang('MemoryUsage').': '.number_format((memory_get_usage()/1048576), 3, '.', '') .' Mb' ;
		echo '<br />';
		echo get_lang('MemoryUsagePeak').': '.number_format((memory_get_peak_usage()/1048576), 3, '.', '').' Mb';
	}
}
?>
<script>
$(document).ready( function() {
	$(".chzn-select").chosen();
});
</script>
</body>
</html>
