<?php
require '../vendor/autoload.php';
$default_ruleset = 'amd056';  // Update when this gets too old

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
$select_rules0 = '<div style="float:left;">';
$select_rules1 = '<div style="float:left;">';
foreach($rules as $fn=>$rule) {
    if($rs==$fn) $checked = 'checked';
    else $checked = '';
    ${"select_rules".(($id-1)%2)} .= "<label for=\"radio{$id}\">{$rule['short']}</label>\n";
    ${"select_rules".(($id-1)%2)} .= "<input type=\"radio\" name=\"ruleset\" value=\"{$fn}\" id=\"radio{$id}\" $checked><br>\n";
    $id++;
}
$select_rules0 .= '</div>';
$select_rules1 .= '</div>';
$select_rules = $select_rules0 . $select_rules1;

// And the callable for the main template to get the results
if($fpath) {
    $results = function() use($rs, $fpath) {
        $old = set_error_handler("grabErrors");
        ob_start(function($buf) {
            $pd = new ParsedownExtra();
            return $pd->text($buf);
        });
        $oc = null;
        try {
            $oc = new OpenCorePlist($fpath);
        } catch(DOMException $e) {
            echo "* <span class=\"err\">Your config.plist contains invalid XML and will not parse</span>\n";
        }
        if($oc) $oc->applyRules(new Rules("../rules/{$rs}.lst"));
        ob_end_flush();
        set_error_handler($old);
    };
    $xml = file_get_contents($fpath);
    // Filter out all potentially sensitive data
    $xml = preg_replace('@(<key>MLB</key>\s*<.*?>)(.*?)(</.*?>)@s', '$1...hidden...$3', $xml);
    $xml = preg_replace('@(<key>BoardSerialNumber</key>\s*<.*?>)(.*?)(</.*?>)@s', '$1...hidden...$3', $xml);
    $xml = preg_replace('@(<key>SystemSerialNumber</key>\s*<.*?>)(.*?)(</.*?>)@s', '$1...hidden...$3', $xml);
    $xml = preg_replace('@(<key>ROM</key>\s*<.*?>)(.*?)(</.*?>)@s', '$1...hidden...$3', $xml);
    $xml = preg_replace('@(<key>SystemUUID</key>\s*<.*?>)(.*?)(</.*?>)@s', '$1...hidden...$3', $xml);
    $xml = preg_replace('@(<key>SystemSKUNumber</key>\s*<.*?>)(.*?)(</.*?>)@s', '$1...hidden...$3', $xml);
    $xml = preg_replace('@(<key>ChassisSerialNumber</key>\s*<.*?>)(.*?)(</.*?>)@s', '$1...hidden...$3', $xml);
    $xml = preg_replace('@(<key>BoardProduct</key>\s*<.*?>)(.*?)(</.*?>)@s', '$1...hidden...$3', $xml);
    $xml = preg_replace('@(<key>BID</key>\s*<.*?>)(.*?)(</.*?>)@s', '$1...hidden...$3', $xml);
    $xml = preg_replace('@(<key>ChassisVersion</key>\s*<.*?>)(.*?)(</.*?>)@s', '$1...hidden...$3', $xml);
    $filtered_xml = htmlspecialchars($xml);
    $links = false;
} else {
    $results = function() { };
    $show_upload = true;
    // Grab the last 10 uploaded files
    $dir = new DirectoryIterator('../uploads');
    $uploads = [];
    foreach ($dir as $fileinfo) {
        if(!$fileinfo->isDot()) {
            $fn = $fileinfo->getFilename();
            $mtime = $fileinfo->getMTime();
            if(time()-$mtime < 14400) {
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

function grabErrors($errno, $errstr, $errfile, $errline) {
    if(preg_match("@DOMDocument::load\(\): (.*?) in.*line: (\d+)@", $errstr, $match)) {
        echo "* <span class=\"err\">".$match[1]." line ".$match[2]."</span>\n";
    }
}

require './main.php';
