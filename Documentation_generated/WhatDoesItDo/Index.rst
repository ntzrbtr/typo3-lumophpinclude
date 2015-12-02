.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _what-does-it-do:

What does it do?
================

This extension lets you include complete external PHP solutions (e.g. message boards or image
galleries) into TYPO3. With this extension you are able to select a local PHP script as well as a
call a script written in any language on a remote server via a real HTTP request.

 <text:title>LumoNet PHP Include</text:title> collects all data sent to the page where the plugin is
used and transfers it to the called script (this only applies to remote calls as local scripts are
just included ans thus have access to all GET, POST and other server data anyway). The user can
choose to have GET data transferred to the called remote script (the transfer of POST data, cookies
and even the handling of file uploads will be realized in future versions).

<text:title>LumoNet PHP Include</text:title> also provides the ability to strip the resulting
content of a called remote app so one can use the PHP app stand-alone as well as integrated in a
TYPO3 website.
