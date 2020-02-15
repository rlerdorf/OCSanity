<?php
require '../vendor/autoload.php';
$default_ruleset = 'amd055';  // Update when this gets too old

// Sanitize user input
$rs = preg_replace('/[^a-zA-Z0-9_]+/', '-', $_GET['rs'] ?? $default_ruleset);
$fn = basename($_GET['file']);
$fpath = __DIR__."/../uploads/$fn";
if(!$fn || !file_exists($fpath)) {
    $fpath = null;
}

// Build the radio button fieldset of rules
$rules = Rules::getList('../rules');
$id = 1;
$select_rules = '';
foreach($rules as $fn=>$rule) {
    if($rs==$fn) $checked = 'checked';
    else $checked = '';
    $select_rules .= "<label for=\"radio{$id}\">{$rule['short']}</label>\n";
    $select_rules .= "<input type=\"radio\" name=\"ruleset\" value=\"{$fn}\" id=\"radio{$id}\" $checked>\n";
    $id++;
}

// And the callable for the main template to get the results
if($fpath) {
    $results = function() use($rs, $fpath) {
        ob_start(function($buf) {
            $pd = new ParsedownExtra();
            return $pd->text($buf);
        });
        $oc = new OpenCorePlist($fpath);
        $oc->parse(new Rules("../rules/{$rs}.lst"));
        ob_end_flush();
    };
} else {
    $results = function() { };
}

require './main.php';
