<?php
namespace Retiolum\Lumophpinclude\Pi1;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Thomas Off <retiolum@googlemail.com>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('lumophpinclude') . 'lib/Snoopy.class.php');

/**
 * Plugin 'PHP Include' for the 'lumophpinclude' extension.
 */
class Plugin extends AbstractPlugin {

	var $prefixId = 'tx_lumophpinclude_pi1'; // Same as class name
	var $scriptRelPath = 'pi1/class.tx_lumophpinclude_pi1.php'; // Path to this script relative to the extension directory
	var $extKey = 'lumophpinclude'; // The extension key

	var $currentUrl; // The URL that is currently used (is determined in doRemoteCall() below)
	var $currentUrlBaseRelative; // The current URL as a base URL for relative links
	var $currentUrlBaseAbsolute; // The current URL as a base URL for absolute links

	/**
	 * Get configuration options from the flexform.
	 *
	 * @return void
	 */
	function init() {
		$this->pi_initPIflexForm(); // Init and get the flexform data of the plugin
		$piFlexForm = $this->cObj->data['pi_flexform']; // Assign the flexform data to a local variable for easier access

		// Get the configuration values from flexform
		// 1. Source sheet
		$this->lConf['source'] = array(
			'script_type' => $this->pi_getFFvalue($piFlexForm, 'script_type', 'sSource'),
			'script_file' => $this->pi_getFFvalue($piFlexForm, 'script_file', 'sSource'),
			'script_url' => $this->pi_getFFvalue($piFlexForm, 'script_url', 'sSource'),
			'transfer_get' => $this->pi_getFFvalue($piFlexForm, 'transfer_get', 'sSource'),
			/*
			'transfer_post'     => $this->pi_getFFvalue($piFlexForm, 'transfer_post', 'sSource'),
			'transfer_cookies'  => $this->pi_getFFvalue($piFlexForm, 'transfer_cookies', 'sSource'),
			*/
		);
		// 2. Processing sheet
		$this->lConf['processing'] = array(
			'strip_non_body' => $this->pi_getFFvalue($piFlexForm, 'strip_non_body', 'sProcessing'),
			'strip_non_marked' => $this->pi_getFFvalue($piFlexForm, 'strip_non_marked', 'sProcessing'),
			'strip_marker' => $this->pi_getFFvalue($piFlexForm, 'strip_marker', 'sProcessing'),
			'wrap_in_div' => $this->pi_getFFvalue($piFlexForm, 'wrap_in_div', 'sProcessing'),
			'rewrite_internal_link' => $this->pi_getFFvalue($piFlexForm, 'rewrite_internal_link', 'sProcessing'),
			/*
			'rewrite_external_link'     => $this->pi_getFFvalue($piFlexForm, 'rewrite_external_link', 'sProcessing'),
			*/
			'rewrite_local_resource' => $this->pi_getFFvalue($piFlexForm, 'rewrite_local_resource', 'sProcessing'),
		);
	}

	/**
	 * Main function; includes the specified script and returns the rendered output.
	 *
	 * @param string $content
	 * @param array $conf : Configuration array
	 * @return string Rendered content from included script
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1; // Configuring so caching is not expected; this value means that no cHash params are ever set; we do this, because it's a USER_INT object!

		// Read FlexForm data
		$this->init();

		// Initialize content variable
		$content = '';

		if ($this->lConf['source']['script_type'] == 'file') {
			// Local script will be included directly
			$content = $this->doLocalCall();
		} else {
			// Remote script will be included via a real HTTP request
			$content = $this->doRemoteCall();
		}

		// Post-process fetched content
		$content = $this->doPostProcessing($content);

		// Return content from script
		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Include a local script resource and return the resulting content for further processing.
	 *
	 * @return string Rendered content from included script
	 */
	function doLocalCall() {
		// Put GET and POST parameters into separate arrays (though the included script can access them anyway)
		$lGetvars = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET();
		$lPostvars = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST();

		// Code to include local scripts; thanks to Peter Klein <peter@umloud.dk>
		ob_start();
		//include('uploads/' . $this->lConf['source']['script_file']);
		include('uploads/tx_lumophpinclude/' . $this->lConf['source']['script_file']);
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
		// Create new Snoopy object for doing remote calls
		$oSnoopy = new \Snoopy();

		// Fetch GET variables using TYPO3 API
		$lGetvars = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET();

		// Determine URL for request
		if (array_key_exists('tx_lumophpinclude_url', $lGetvars)
			&& $lGetvars['tx_lumophpinclude_url'] != ''
		) {
			// If parameter exists => decode URL and check signature
			$url = base64_decode($lGetvars['tx_lumophpinclude_url']);
			if (array_key_exists('tx_lumophpinclude_signature', $lGetvars)
				&& $lGetvars['tx_lumophpinclude_signature'] != ''
			) {
				$signature = $lGetvars['tx_lumophpinclude_signature'];
				if (\TYPO3\CMS\Core\Utility\GeneralUtility::hmac($url) !== $signature) {
					return '<p>Signature check for URL was not passed – request denied due to possible security issues.</p>';
				} else {
					$this->currentUrl = $url;
				}
			} else {
				return '<p>No signature found in URL – request denied due to possible security issues.</p>';
			}
		} else {
			// No parameter is set => use value from the flexform
			$this->currentUrl = $this->lConf['source']['script_url'];
		}

		// Determine relative and absolute base URLs
		$lUrlParts = parse_url($this->currentUrl);
		$urlPath = (array_key_exists('path', $lUrlParts) ? preg_replace('/[^\/]*$/', '', $lUrlParts['path']) : '');
		$urlPort = (array_key_exists('port', $lUrlParts) ? ':' . $lUrlParts['path'] : '');
		$baseUrl = $lUrlParts['scheme'] . '://' . $lUrlParts['host'] . $urlPort;
		$this->currentUrlBaseAbsolute = $baseUrl . '/';
		$this->currentUrlBaseRelative = $baseUrl . $urlPath;

		// Compose the full URL for the request
		if ($this->lConf['source']['transfer_get']) {
			// Add GET variables to the base URL
			$params = '';
			foreach ($lGetvars as $key => $val) {
				// Omit some parameters which are either TYPO3 or extension based
				$lExcludeKeys = array(
					'id',
					'tx_lumophpinclude_url',
					'tx_lumophpinclude_signature',
				);
				if (in_array($key, $lExcludeKeys)) {
					continue;
				}

				// Append parameters to the $params string
				if (is_array($val)) {
					foreach ($val as $key2 => $val2) {
						$params .= $key . '[]' . '=' . urlencode($val2) . '&';
					}
				} else {
					$params .= $key . '=' . urlencode($val) . '&';
				}
			}

			// Remove the last ampersand character
			$params = substr($params, 0, -1);

			// Append parameter string to base URL
			$url = $this->currentUrl . ($params == '' ? '' : ((strstr($baseUrl, '?') ? '&' : '?') . $params));
		} else {
			// No more parameters to add => use base URL determined above
			$url = $this->currentUrl;
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
	 * @param string $content : The fetched content of the included script
	 * @return string Content after post-processing
	 */
	function doPostProcessing($content) {
		// Strip non-body parts
		if ($this->lConf['processing']['strip_non_body']) {
			// Remove everything before and after body tag
			if (preg_match('/<body(\s[^>]*)?>\s*(.*?)\s*<\/body>/si', $content, $matches)) {
				$content = $matches[2];
			}
		}

		// Strip non-marked parts
		if ($this->lConf['processing']['strip_non_marked']) {
			// Strip content outside marked area if marker is set in flexform
			$marker = $this->lConf['processing']['strip_marker'];
			if ($marker != '') {
				$content = preg_replace('/^.*?<!--\s*' . $marker . '\s*-->/s', '', $content);
				$content = preg_replace('/<!--\s*' . $marker . '\s*-->.*/s', '', $content);
			}
		}

		// Link rewriting and resource rewriting is only done for remote scripts
		if ($this->lConf['source']['script_type'] == 'url') {
			// Do link rewriting of internal links (i.e. links relative to the currently included script)
			if ($this->lConf['processing']['rewrite_internal_link']) {
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
								$url = $lUrlMatches[2]; // The URL of the link

								// Determine final URL based on the link to follow
								$baseUrl = $this->currentUrl; // Base URL as determined in doRemoteCall()
								if (substr($url, 0, 1) == '/') {
									// Absolute URL
									$rewrittenUrl = $this->currentUrlBaseAbsolute . $url;
								} else {
									// URL relative to original script
									$rewrittenUrl = $this->currentUrlBaseRelative . $url;
								}

								// Add the URL as a parameter and make the URL relative to the current page (i.e. the TYPO3 page)
								$rewrittenUrl = \TYPO3\CMS\Core\Utility\GeneralUtility::linkThisScript(array(
									'tx_lumophpinclude_url' => base64_encode($rewrittenUrl),
									'tx_lumophpinclude_signature' => \TYPO3\CMS\Core\Utility\GeneralUtility::hmac($rewrittenUrl),
								));

								// Add an entry to the replace array (used below to do the real work)
								$lReplaces[$match] = str_replace($url, $rewrittenUrl, $match);
							}
						}
					}
				}

				// Do the real replacement work using the above created array
				$content = str_replace(array_keys($lReplaces), array_values($lReplaces), $content);
			}

			/*
			// Do link rewriting of external links (i.e. links that would leave the currently included script)
			if ($this->lConf['processing']['rewrite_external_link']) {
				// TODO: Implement external link rewriting similar to internal rewriting
			}
			*/

			// Rewrite local image and script resources
			if ($this->lConf['processing']['rewrite_local_resource']) {
				// Initialize arrays for replacing
				$lReplaces = array();

				// Search all "src" attributes
				$lMatches = array();
				if (preg_match_all('/(src=(["\']?)([^\s>]*)\\2)/', $content, $lMatches) > 0) {
					// Process matches
					for ($i = 0; $i < count($lMatches[3]); $i++) {
						$match = $lMatches[1][$i];
						$src = $lMatches[3][$i];

						// Skip non-local resources
						if (preg_match('/^((http|https|ftp):\/\/|mailto:|javascript:)/', $src)) {
							continue;
						}

						// Determine final URL based on the attribute's value
						$baseUrl = $this->currentUrl;
						if (substr($src, 0, 1) == '/') {
							// Absolute URL
							$rewrittenSrc = $this->currentUrlBaseAbsolute . $src;
						} else {
							// URL relative to original script
							$rewrittenSrc = $this->currentUrlBaseRelative . $src;
						}

						// Add an entry to the replace array (used below to do the real work)
						$lReplaces[$match] = str_replace($src, $rewrittenSrc, $match);
					}
				}

				// Do the real replacement work using the above created array
				$content = str_replace(array_keys($lReplaces), array_values($lReplaces), $content);
			}
		}

		// Wrap all content in div with class
		if ($this->lConf['processing']['wrap_in_div']) {
			// Create classname using an MD5 hash of the included script
			$classname = 'tx_lumophpinclude_' . md5($this->lConf['source']['script_file']);
			$content = '<div class="' . $classname . '">' . $content . '</div>';
		}

		// Return the processed content
		return $content;
	}

}
