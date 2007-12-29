<?php
/*
 * Copyright notice
 *
 * (c) 2005-2008 Thomas Off <typo3@retiolum.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

require_once(PATH_tslib.'class.tslib_pibase.php');

/**
 * Plugin 'PHP Include' for the 'lumophpinclude' extension.
 *
 * @author Thomas Off <typo3@retiolum.de>
 * @package TYPO3
 * @subpackage tx_lumogooglemaps
 */
class tx_lumophpinclude_pi1 extends tslib_pibase {

    var $prefixId = 'tx_lumophpinclude_pi1';            // Same as class name
    var $scriptRelPath = 'pi1/class.tx_lumophpinclude_pi1.php';    // Path to this script relative to the extension dir.
    var $extKey = 'lumophpinclude';    // The extension key.

    /**
     * Get configuration options from the flexform.
     *
     * @return void
     */
    function init() {
        $this->pi_initPIflexForm();    // Init and get the flexform data of the plugin.
        $piFlexForm = $this->cObj->data['pi_flexform'];    // Assign the flexform data to a local variable for easier access.

        // Get the configuration values from flexform.
        $this->lConf['transfer_get'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'transfer_get', 'sDEF');
        $this->lConf['transfer_post'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'transfer_post', 'sDEF');
        $this->lConf['transfer_cookies'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'transfer_cookies', 'sDEF');
        $this->lConf['script_type'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'script_type', 'sDEF');
        $this->lConf['script_file'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'script_file', 'sDEF');
        $this->lConf['script_url'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'script_url', 'sDEF');
        $this->lConf['strip_non_body'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'strip_non_body', 'sDEF');
        $this->lConf['strip_non_marked'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'strip_non_marked', 'sDEF');
        $this->lConf['marker_start'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'marker_start', 'sDEF');
        $this->lConf['marker_end'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'marker_end', 'sDEF');
        $this->lConf['wrap_in_div'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'wrap_in_div', 'sDEF');
        $this->lConf['replace_pid'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'replace_pid', 'sDEF');
    }

    /**
     * Main function; includes the specified script and returns the rendered output.
     *
     * @param string $content
     * @param array $conf: Configuration array
     * @return string Rendered content from included script
     */
    function main($content, $conf) {
        $this->conf = $conf;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();
        $this->pi_USER_INT_obj = 1;    // Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!

        // Read FlexForm data.
        $this->init();

        // Initialize content variable.
        $content = '';

        if ($this->lConf['script_type'] == 'file') {
            // Local script will be included directly.
            $content = $this->doLocalCall();
        }
        else {
            // Remote script will be included via a real HTTP request.
            $content = $this->doRemoteCall();

        }

        // Strip non-body parts
        if ($this->lConf['strip_non_body']) {
            // Remove everything before and after body tag.
            preg_match('/<body\b[^>]*>\s*(.*?)\s*<\/body>/si',    $content, $matches);
            $content = ($matches[1]) ? $matches[1] : $content;
        }

        // Strip non-marked parts
        if ($this->lConf['strip_non_marked']) {
            // Strip start and end if marker are set in flexform.
            if ($this->lConf['marker_start'] != '') {
                $content = preg_replace('/.*?<!-- *' . $this->lConf['marker_start'] . ' *-->/s', '$1', $content);
            }
            if ($this->lConf['marker_end'] != '') {
                $content = preg_replace('/<!-- *' . $this->lConf['marker_end'] . ' *-->.*/s', '$1', $content);
            }
        }

        // Wrap all content in div with class
        if ($this->lConf['wrap_in_div']) {
            // Create classname based on name of PHP file.
            $path = explode("?", $this->lConf['script_file']);
            $basename = basename($path[0]);

            // Change any non letter, hyphen, or period to an underscore.
            $pattern = array(
                '/[^\w]/s',
                '/\./s'
                );
            $replace = array (
                '_',
                '_'
                );
            $class = preg_replace($pattern, $replace, $basename);
            $content = '<div class="tx_lumophpinclude_' . $class . '">' . $content . '</div>';
        }

        // Replace marker for page id
        if ($this->lConf['replace_pid_marker']) {
            // Replace special markers.
            $page_id = $GLOBALS['TSFE']->id;
            $content = preg_replace('/###PID###/', $page_id, $content);
        }

        // Return content from script.
        return $this->pi_wrapInBaseClass($content);
    }
    
    /**
     * Include a local script resource and return the resulting content for further processing.
     *
     * @return string Rendered content from included script
     */
    function doLocalCall() {
        // Put GET and POST parameters into separate arrays (though the included script can access them anyway).  
        $getvars = t3lib_div::_GET();
        $postvars = t3lib_div::_POST();

        /*
         * Old original code to include local scripts.
         */
        /*
        // Get script content.
        $script_content = $this->cObj->fileResource('uploads/' . $this->lConf['script_file']);

        // Remove PHP start and end tags from script content.
        $script_content = preg_replace('/<\?php/', '', $script_content);
        $script_content = preg_replace('/\?>/', '', $script_content);

        // Start output buffering.
        ob_start();

        // Evaluate included code.
        eval($script_content);

        // Get output from evaluated code.
        $content = ob_get_contents();

        // End output buffering and clean buffer.
        ob_end_clean();
        */

        /*
         * New code to include local scripts; thanks to Peter Klein <peter@umloud.dk>
         */
        // Get script content.
        ob_start();
        include('uploads/' . $this->lConf['script_file']);
        $content = ob_get_contents();
        ob_end_clean();
        
        // Return content for further processing.
        return $content;
    }
    
    /**
     * Include a remote script resource via a real HTTP request and return the resulting content for further processing.
     *
     * @return string Rendered content from included script
     */
    function doRemoteCall() {
        // Turn GET parameters into single string.
        $temp_getvars = '';
        if ($this->lConf['transfer_get']) {
            foreach ($_GET as $key => $val) {
                if (is_array($val)) {
                    $i = 0;
                    foreach ($val as $key2 => $val2) {
                        $temp_getvars .= $key . '[]' . '=' . urlencode($val2) . '&';
                        $i++;
                    }
                }
                else {
                    $temp_getvars .= $key . '=' . urlencode($val) . '&';
                }
            }
        }

        // Turn POST parameters into single string.
        $temp_postvars = '';
        if ($this->lConf['transfer_post']) {
            foreach ($_POST as $key => $val) {
                if (is_array($val)) {
                    $i = 0;
                    foreach ($val as $key2 => $val2) {
                        $temp_postvars .= $key . '[]' . '=' . urlencode($val2) . '&';
                        $i++;
                    }
                }
                else {
                    $temp_postvars .= $key . '=' . urlencode($val) . '&';
                }
            }
        }

        // Turn cookie data into single string.
        $temp_cookievars = '';
        if ($this->lConf['transfer_cookies']) {
            foreach ($_COOKIE as $key => $val) {
                if (is_array($val)) {
                    $i = 0;
                    foreach ($val as $key2 => $val2) {
                        $temp_cookievars .= $key . '[]' . '=' . urlencode($val2) . '&';
                        $i++;
                    }
                }
                else {
                    $temp_cookievars .= $key . '=' . urlencode($val) . '&';
                }
            }
        }

        // Compose GET and POST parameter and cookie data into one string.
        $params = '';
        if ($temp_getvars != '') {
            $params .= ($params == '' ? '' : '&') . $temp_getvars;
        }
        if ($temp_postvars != '') {
            $params .= ($params == '' ? '' : '&') . $temp_postvars;
        }
        if ($temp_cookievars != '') {
            $params .= ($params == '' ? '' : '&') . $temp_cookievars;
        }

        // Compose URL of script to include.
        $url = $this->lConf['script_url'];
        $url .= ($params == '' ? '' : ((strstr($url, '?') ? '&' : '?') . $params));

        // Include script.
        $content = file_get_contents($url);

        // Return content for further processing.
        return $content;
    }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/lumophpinclude/pi1/class.tx_lumophpinclude_pi1.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/lumophpinclude/pi1/class.tx_lumophpinclude_pi1.php']);
}

?>
