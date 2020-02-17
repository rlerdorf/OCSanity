<?php
require '../vendor/autoload.php';
$default_ruleset = 'amd055';  // Update when this gets too old

// Support old-style URL oc param

if(empty($_GET['rs']) && !empty($_GET['oc'])) {
    $rs = ($_GET['oc'] == '0.5.6') ? 'amd056' : 'amd055';
} else {
// Sanitize user input
    $rs = preg_replace('/[^a-zA-Z0-9_]+/', '-', $_GET['rs'] ?? $default_ruleset);
}
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
        $oc->applyRules(new Rules("../rules/{$rs}.lst"));
        ob_end_flush();
    };
    $links = false;
} else {
    $results = function() { };
    $show_upload = true;
    // Grab the last 10 uploaded files
    $dir = new DirectoryIterator('../uploads');
    foreach ($dir as $fileinfo) {
        if(!$fileinfo->isDot()) {
            $fn = $fileinfo->getFilename();
            $mtime = $fileinfo->getMTime();
            if(time()-$mtime < 7200) {
                $uploads[$fn] = $mtime;
            }
        }
    }
    arsort($uploads);
    $links = '';
    foreach($uploads as $fn=>$mtime) {
        foreach($rules as $rf=>$rule) {
            if(strpos($fn, $rf) === 0) $links.= "<a href=\"/?file={$fn}&rs={$rf}\"\>{$rule['short']} (<time class=\"timeago\" datetime=\"".date('c',$mtime)."\">".date('r')."</time>)</a><br>\n";
        }
    }
    $links .= "<br>\n";
}

require './main.php';
