<?php

class OpenCorePlist extends CFPropertyList\CFPropertyList {
    function __construct(string $filename) {
        if(!file_exists($filename)) {
            throw new \Exception("File not found - ".getcwd()." $filename ");
        }
        parent::__construct($filename, CFPropertyList\CFPropertyList::FORMAT_XML);
    }

    function applyRules(Rules $rules) {
        $seen_title = [];
        // Check for missing groups
        foreach(array_keys(array_diff_key($rules->rule, $this->toArray())) as $missing_group) {
            $this->print_msg("-*$missing_group* group is missing");
        }
        // Check for missing sections
        $confArray = $this->toArray();
        foreach($rules->rule as $group=>$block) {
            foreach($block as $k=>$v) {
                if($k == 'top' || $k[1]==':') continue;
                if(!array_key_exists(trim($k,':'), $confArray[$group])) {
                    $this->print_msg("!**$group** - **".trim($k,':')."** section is missing");
                }
            }
        }
        foreach($this->toArray() as $group=>$d) {
            if (!array_key_exists($group, $rules->rule)) {
                $this->print_msg("-*$group* this shouldn't be here");
                continue;
            }
            echo "\n## $group\n";
            if(!empty($rules->rule[$group]["top"])) {
                foreach($rules->rule[$group]["top"] as $rule) {
                    if(!empty($rule)) {
                        $msgs = $rule->exec($d, $this->value[0]->{$group});
                        if($rule->title && empty($seen_title[$rule->title])) {
                            echo "\n###".$rule->title."\n";
                            $seen_title[$rule->title] = true;
                        }
                        foreach($msgs as $k=>$msg) {
                            if(!empty($msg) && $msg!='""') {
                                $this->print_msg($msg);
                                // Make sure we don't match more rules to the same entry
                                if(is_int($k) && $k) {
                                    unset($d[$k-1]);
                                }
                                // @phan-suppress-next-line PhanTypeArraySuspicious
                                else if($k[0]==':') {
                                    // @phan-suppress-next-line PhanTypeMismatchArgumentInternal
                                    [,$sec,$tk] = explode(':',$k,3);
                                    unset($d[$sec][$tk]);
                                }
                                else if($k) unset($d[$k]);
                            }
                        }
                    }
                }
            }

            foreach($d as $section=>$dd) {
                if(empty($seen_title[$section])) {
                    echo "\n###$section\n";
                    $seen_title[$section] = true;
                }
                if(!empty($rules->rule[$group][":$section"])) {
                    foreach($rules->rule[$group][":$section"] as $rule) {
                        if(!empty($rule)) {
                            $msgs = $rule->exec($dd, $this->value[0]->{$group}->{$section});
                            foreach($msgs as $k=>$msg) {
                                if(!empty($msg) && $msg!='""') {
                                    $this->print_msg($msg);
                                    if(is_int($k) && $k) unset($dd[$k-1]);  // Make sure we don't match more rules to the same entry
                                    else if($k) unset($dd[$k]);
                                }
                            }
                        }
                    }
                }
                // Check if there are rules hiding in a sub-section
                if(is_array($dd)) {
                    foreach($dd as $ssection=>$ddd) {
                        if(!empty($rules->rule[$group]["::$ssection"])) {
                            foreach($rules->rule[$group]["::$ssection"] as $rule) {
                                if(!empty($rule)) {
                                    $msgs = $rule->exec($ddd, $this->value[0]->{$group}->{$section}->{$ssection}, false);
                                    foreach($msgs as $k=>$msg) {
                                        if(!empty($msg) && $msg!='""') {
                                            $this->print_msg($msg);
                                            if(is_int($k) && $k) unset($ddd[$k-1]);  // Make sure we don't match more rules to the same entry
                                            else if($k) unset($ddd[$k]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $seen_title = [];
        }
    }

    function print_msg($msg) {
        $msg = trim($msg,'"');
        switch($msg[0]) {
            case ' ': $sev = 'good'; $icon = 'fa-check-circle'; $msg = substr($msg,1); break;
            case '-': $sev = 'warn'; $icon = 'fa-question-circle'; $msg = substr($msg,1); break;
            case '!': $sev = 'err'; $icon = 'fa-times-circle'; $msg = substr($msg,1); break;
            case '%': $sev = 'info'; $icon = 'fa-info-circle'; $msg = substr($msg,1); break;
            default: $sev = 'good'; break;
        }
        if(preg_match('/{\$([^}]+)}/', $msg, $match)) {
            echo "* <span class=\"err fas fa-times-circle\">Unexpected missing **{$match[1]}** here</span>\n";
        } else {
            echo "* <span class=\"{$sev} fas {$icon}\">$msg</span>\n";  // Markdown-extra to add the severity class - see main.css
        }
    }
}
