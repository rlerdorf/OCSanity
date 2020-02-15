<?php
namespace CFPropertyList;

class CFPropertyList implements \Iterator {
    const FORMAT_AUTO = 0;
    const FORMAT_BINARY = 1;
    const FORMAT_XML = 2;
    protected $value = [];
    public function current() { return 1; }
    public function key() { return 1; }
    public function next() {  }
    public function valid() { return true; }
    public function rewind() { }
    public function __construct($file=null,$format=self::FORMAT_AUTO) { }
    /** @return mixed */
    public function toArray() { return 1; }
}
abstract class CFType { }
class CFData extends CFType { }
class CFString extends CFType { }
class CFNumber extends CFType { }
class CFBoolean extends CFType { }
