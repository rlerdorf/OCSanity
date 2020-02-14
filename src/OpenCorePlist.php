<?php
class OpenCorePlist {
    /** @var CFPropertyList */
    private $pList;
    /** @var array */
    private $pArray;
    /** @var string */
    protected $group;
    /** @var string */
    protected $section;

    function __construct(string $filename) {
        if(!file_exists($filename)) {
            throw new \Exception('File not found');
            return;
        }
        $this->pList = new \CFPropertyList\CFPropertyList($filename, \CFPropertyList\CFPropertyList::FORMAT_XML); 
        $this->pArray = $this->pList->toArray();
    }

    function parse(Rules $rules) {
        $last_title = '';
        foreach($this->pArray as $group=>$d) {
            $this->group = $group;
            if (!array_key_exists($group, $rules->rule)) {
                $this->print_msg("-*$group* this shouldn't be here");
                continue;
            }
            echo "\n## $group\n";
            if(!empty($rules->rule[$group]["top"])) {
                foreach($rules->rule[$group]["top"] as $rule) {
                    if(!empty($rule)) {
                        $msgs = $rule->exec($d); 
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
                                } else if($k[0]==':') {
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
                $this->section = ":$section";
                if($last_title!=$section) {
                    echo "\n###$section\n";
                    $last_title = $section;
                }
                if(!empty($rules->rule[$group][":$section"])) {
                    foreach($rules->rule[$group][":$section"] as $rule) {
                        if(!empty($rule)) {
                            $msgs = $rule->exec($dd); 
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
                                    $msgs = $rule->exec($ddd, false);
                                    foreach($msgs as $k=>$msg) {
                                        if(!empty($msg)) {
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
