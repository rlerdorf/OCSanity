[![Build Status](https://travis-ci.org/rlerdorf/OCSanity.svg?branch=master)](https://travis-ci.org/rlerdorf/OCSanity)

# OpenCore Sanity Checker

This provides a way to apply rulesets to OpenCore config.plist files.

The rule sets are written in a simplified schema language loosely based on PHP
and using markdown for output.

The general structure of a rule set file is:

```
# Comment
=Output this line
=[doclink]:https://example.com

ACPI
:Add
 count==0 " True Output":"!False output"
 [Attribute]==Something "!Don't set {$Attribute} to Something here. Please review [Docs][doclink]"
 [Attribute]!=FakeSMC.kext|VirtualSMC.kext "-You should have either FakeSMC.kext or VirtualSMC.kext here"
 [Attribute]~=".dsl$" "!{$Attribute} do not include .dsl files here"
 [Attribute]==* " Attribute set to {$Attribute}"

:Quirks
 Setting1=yes
 Setting2=no
 Setting3=Whatever " {$setting} set to {$value} Great you got this one right"
 Setting4=123 :"-{$setting{ set to {$value}. If you don't set {$setting} to 123 bad things happen."
```

The main non-self explanatory thing in the above is that the first character of the true/false strings
define the severity. A ***\<space\>*** means *good* (green), a hyphen ***-*** is a *warning* (yellow), an
explanation mark ***!*** is an error (red), and a percent sign *%* is a blue info message.

See the rules directory for full examples.

## Installation

`composer install` then run `vendor/bin/phpunit` to run the unit tests and `vendor/bin/phan` to run a static analysis scan.

`./ocs -m -r rules/amd055.lst Sample.plist` to run a rule against a plist file on the command line with output in markdown format.

For a web install, point set your docroot to the htdocs directory and make sure you have PHP 7.x enabled.

You can see it running at https://opencore-dev.slowgeek.com
