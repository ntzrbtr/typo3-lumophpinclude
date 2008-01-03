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

require_once(t3lib_extMgm::extPath('lumophpinclude') . 'lib/Snoopy.class.php');

/**
 * Plugin 'PHP Include' for the 'lumophpinclude' extension.
 *
 * @author Thomas Off <typo3@retiolum.de>
 * @package TYPO3
 * @subpackage tx_lumogooglemaps
 */
class tx_lumophpinclude_pi1 extends tslib_pibase {

    var $prefixId = 'tx_lumophpinclude_pi1'; // Same as class name
    var $scriptRelPath = 'pi1/class.tx_lumophpinclude_pi1.php'; // Path to this script relative to the extension dir.
    var $extKey = 'lumophpinclude'; // The extension key.

    /**
     * Get configuration options from the flexform.
     *
     * @return void
     */
    function init() {
        $this->pi_initPIflexForm(); // Init and get the flexform data of the plugin.
        $piFlexForm = $this->cObj->data['pi_flexform']; // Assign the flexform data to a local variable for easier access.

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
        $this->pi_USER_INT_obj = 1; // Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!

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

        // Post-process fetched content
        $content = $this->doPostProcessing($content);

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
        $lGetvars = t3lib_div::_GET();
        $lPostvars = t3lib_div::_POST();
        
        // Code to include local scripts; thanks to Peter Klein <peter@umloud.dk>
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
        /*
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
        */

        // Create new Snoopy object for doing remote calls
        $oSnoopy = new Snoopy();
        
        // Fetch GET variables using TYPO3 API
        $lGetvars = t3lib_div::_GET();
        
        // Determine URL for request
        $baseUrl = $this->lConf['script_url']; // Base URL as set in flexform
        if (array_key_exists('tx_lumophpinclude_url', $lGetvars)
            && $lGetvars['tx_lumophpinclude_url'] != '') {
            // If parameter exists => decode URL
            $url = base64_decode($lGetvars['tx_lumophpinclude_url']);

            // Determine final URL based on the link to follow
            if (substr($url, 0, 1) == '/') {
                // Absolute URL
                $baseUrl = preg_replace('/^(https?:\/\/[^\/]+).*/', '$1', $baseUrl);
            }
            else {
                // URL relative to original script
                $baseUrl = substr($baseUrl, 0, strrpos($baseUrl, '/'));
            }
            
            // Append the link to the base URL
            $baseUrl .= $url;
        }
        
        // Compose the full URL for the request
        if ($this->lConf['transfer_get']) {
            // Add GET variables to the base URL
            $params = '';
            foreach ($lGetvars as $key => $val) {
                // Omit the id parameter as this is just the TYPO3 page id
                if ($key == 'id') {
                    continue;
                }
                
                // Append parameters to the $params string
                if (is_array($val)) {
                    foreach ($val as $key2 => $val2) {
                        $params .= $key . '[]' . '=' . urlencode($val2) . '&';
                    }
                }
                else {
                    $params .= $key . '=' . urlencode($val) . '&';
                }
            }
            
            // Remove the last ampersand character
            $params = substr($params, 0, -1);
            
            // Append parameter string to base URL
            $url = $baseUrl . ($params == '' ? '' : ((strstr($baseUrl, '?') ? '&' : '?') . $params));
        }
        else {
            // No more parameters to add => use base URL determined above
            $url = $baseUrl;
        }
        
        // Fetch the URL
        if ($oSnoopy->fetch($url)) {
            $content = $oSnoopy->results;
        }

        // Return content for further processing.
        return $content;
    }
    
    /**
     * Do post-processing of the fetched content, i.e. link rewriting, stripping, etc.
     *
     * @param string $content: The fetched content of the included script
     * @return string Content after post-processing
     */
    function doPostProcessing($content) {
        // Strip non-body parts
        if ($this->lConf['strip_non_body']) {
            // Remove everything before and after body tag.
            preg_match('/<body\b[^>]*>\s*(.*?)\s*<\/body>/si', $content, $matches);
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
        
        // EXPERIMENTAL: URL REWRITING FOR LOCAL LINKS
$rewriteLocalUrl = true;
        if ($rewriteLocalUrl) {
            // Initialize arrays for replacing
            $lReplaces = array();
            
            // Search all links
            $lMatches = array();
            if (preg_match_all('/(<a[^>]+>)/', $content, $lMatches) > 0) {
                // Process matches
                $lMatches = $lMatches[1];
                foreach ($lMatches as $match) {                    
                    // Search for all links with a "href" attribute
                    $lSubmatches = array();
                    if (preg_match('/(href=(["\']?)([^\s>]*)\\2)/', $match, $lSubmatches)) {
                        $submatch = $lSubmatches[1]; // The whole match
                        $enclosure = $lSubmatches[2]; // The enclosure of the attribute value if present
                        $url = $lSubmatches[3]; // The URL of the link

                        // Process all URLs that are local links (i.e. that do not have a protocol specifier) 
                        $lUrlMatches = array();
                        if (preg_match('/^(?(?!(http|https|ftp):\/\/|mailto:|javascript:)(.*))$/', $url, $lUrlMatches)) {
                            $url = ($enclosure != '' ? substr($lUrlMatches[2], 0, -1) : $lUrlMatches[2]); // The URL of the link with the enclosure stripped
                            
                            // Add the URL as a parameter and make the URL relative to the current page (i.e. the TYPO3 page)
                            $rewrittenUrl = $_SERVER['REQUEST_URI'] . '&tx_lumophpinclude_url=' . base64_encode($url);
                            
                            // Add an entry to the replace array (used below to do the real work)
                            $lReplaces[$match] = str_replace($url, $rewrittenUrl, $match);
                        }
                    }
                }
            }
            
            // Do the real replacement work using the above created array
            $content = str_replace(array_keys($lReplaces), array_values($lReplaces), $content);
        }
                
        // Return the processed content
        return $content;
    }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/lumophpinclude/pi1/class.tx_lumophpinclude_pi1.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/lumophpinclude/pi1/class.tx_lumophpinclude_pi1.php']);
}

?>
