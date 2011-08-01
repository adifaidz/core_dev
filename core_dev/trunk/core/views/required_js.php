<?php
/**
 * This is called for all page requests
 *
 * defines a minimal set of core functions
 */

$header->registerJsFunction(
// Makes element with name "n" visible in browser
'function show_el(n)'.
'{'.
    'var e=document.getElementById(n);'.
    'e.style.display="";'.
'}'
);

$header->registerJsFunction(
// Makes element with name "n" invisible in browser
'function hide_el(n)'.
'{'.
    'var e=document.getElementById(n);'.
    'e.style.display="none";'.
'}'
);

$header->registerJsFunction(
// Toggles element with name "n" between visible and hidden
'function toggle_el(n)'.
'{'.
    'var e=document.getElementById(n);'.
    'e.style.display=(e.style.display?"":"none");'.
'}'
);

?>