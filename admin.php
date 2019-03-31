<?php

/**
 * Internal Filebrowser -- admin.php
 *
 * @category  CMSimple_XH
 * @package   CALDav Calendar
 * @author    Thomas Winkler <thomas.winkler@iggmp.net>
 * @copyright 2018 nibble-arts <http://www.nibble-arts.org>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

/*
 * Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/*
 * Register the plugin menu items.
 */
if (function_exists('XH_registerStandardPluginMenuItems')) {
    XH_registerStandardPluginMenuItems(true);
}

if (function_exists('caldav_calendar') 
    && XH_wantsPluginAdministration('caldav_calendar') 
    || isset($visitors_online) && $visitors_online == 'true')
{

    $o .= print_plugin_admin('off');

    switch ($admin) {

	    case '':
	        $o .= '<h1>CalDAV Kalender</h1>';
    		$o .= '<p>Version 0.9</p>';
            $o .= '<p>Copyright 2018</p>';
    		$o .= '<p><a href="http://www.nibble-arts.org" target="_blank">Thomas Winkler</a></p>';
            $o .= '<p>Mit dem Plugin kann ein Kalender eines CalDAV-Server eingebunden werden. In der Konfiguration können die Zugangsdaten des Server eingegeben, im Pluginaufruf unterschiedliche Kalender des Benutzers angezeigt werden. Über eine Formatangabe lassen sich unterschiedliche Darstellungen wählen und mit einem Filter können bestimmte Datumsbereiche gefilter werden.</p>';

            $o .= "<h3>Verwendung</h3>";

            $o .= "<p>An der Stelle, an der ein Kalender angezeigt werden soll, wird folgender Aufruf eingefügt:</p>";
            $o .= '<p class="code">{{{plugin:caldav_calendar("Kalendername"[,"Format","Filter"];}}}</p>';

	        break;

	    default:
	        $o .= plugin_admin_common($action, $admin, $plugin);
    }

}
?>
