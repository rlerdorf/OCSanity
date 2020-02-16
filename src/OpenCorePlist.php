<?php

class OpenCorePlist extends CFPropertyList\CFPropertyList {
    function __construct(string $filename) {
        if(!file_exists($filename)) {
            throw new \Exception("File not found - ".getcwd()." $filename ");
        }
        parent::__construct($filename, CFPropertyList\CFPropertyList::FORMAT_XML);
    }

    function applyRules(Rules $rules) {
        $last_title = '';
        foreach(array_keys(array_diff_key($rules->rule, $this->toArray())) as $missing_group) {
            $this->print_msg("-*$missing_group* group is missing");
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
                        if($rule->title && $last_title!=$rule->title) {
                            echo "\n###".$rule->title."\n";
                            $last_title = $rule->title;
                        }
                        foreach($msgs as $k=>$msg) {
                            if(!empty($msg)) {
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
                if($last_title!=$section) {
                    echo "\n###$section\n";
                    $last_title = $section;
                }
                if(!empty($rules->rule[$group][":$section"])) {
                    foreach($rules->rule[$group][":$section"] as $rule) {
                        if(!empty($rule)) {
                            $msgs = $rule->exec($dd, $this->value[0]->{$group}->{$section});
                            foreach($msgs as $k=>$msg) {
                                if(!empty($msg)) {
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
        }
    }

    function get_obj(...$args) {
        switch(count($args)) {
            case 0:
                return $this->value[0];
            case 1:
                return $this->value[0]->{$args[0]};
            case 2:
                return $this->value[0]->{$args[0]}->{$args[1]};
            case 3:
                return $this->value[0]->{$args[0]}->{$args[1]}->{$args[2]};
            case 4:
                return $this->value[0]->{$args[0]}->{$args[1]}->{$args[2]}->{$args[3]};
            case 5:
                return $this->value[0]->{$args[0]}->{$args[1]}->{$args[2]}->{$args[3]}->{$args[4]};
        }
    }

    function print_msg($msg) {
        $msg = trim($msg,'"');
        switch($msg[0]) {
            case ' ': $sev = 'good'; $msg = substr($msg,1); break;
            case '-': $sev = 'warn'; $msg = substr($msg,1); break;
            case '!': $sev = 'err'; $msg = substr($msg,1); break;
            default: $sev = 'good'; break;
        }
        echo "* <span class=\"{$sev}\">$msg</span>\n";  // Markdown-extra to add the severity class - see main.css
    }
}
