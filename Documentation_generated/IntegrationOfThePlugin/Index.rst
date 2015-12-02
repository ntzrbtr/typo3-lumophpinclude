.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _integration-of-the-plugin:

Integration of the Plugin
=========================

#. Add a new content element of type plugin „PHP Include“ to the desired page.
#. Choose the type of included script, enter the URL or select/upload a local file and set the
   desired parameters. If you call a remote script, you might want to check the „Strip non-body
   parts“ marker to get a valid HTML result page. If you even have markers set in the script, you
   can use the „Strip content outside markers“ option along with the input field labeled
   „Marker for stripping“. The marker must be of the form  <!-- MARKER --> **(Info: This marker
   is not related in any way to the commonly used markes in extension or page templates!)** 
#. To adjust the display of the included code you can use „Wrap output with div“ and have another
   div tag wrapped around the content to apply additional CSS directives on it. The class of the div
   tag is tx_lumophpinclude_<MD5 hash of included file name>
#. Call the page in the frontend and check the results.
