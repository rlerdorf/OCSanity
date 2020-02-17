[![Build Status](https://travis-ci.org/rlerdorf/OCSanity.svg?branch=master)](https://travis-ci.org/rlerdorf/OCSanity)

# OpenCore Sanity Checker

OCSanity provides a way to apply rulesets to OpenCore config.plist files.

The rulesets are written in a simplified schema language loosely based on PHP
and using markdown for output. For the structure of the plist file parsed by OpenCore, the
terminology I use (which isn't based on anything) is groups, sections, sub-sections, settings and attributes.

For a snippet of the Sample.plist file which looks like this:

```xml
<dict>
    <key>ACPI</key>
    <dict>
        <key>Add</key>
        <array>
            <dict>
                <key>Comment</key>
                <string>My custom DSDT</string>
                <key>Enabled</key>
                <false/>
                <key>Path</key>
                <string>DSDT.aml</string>
            </dict>
        </array>
    </dict>
    <key>Quirks</key>
    <dict>
        <key>FadtEnableReset</key>
        <false/>
    </dict>
```

Here **ACPI** is the top-level group, **Add** is the section. And then each array element in the section has attributes **Comment**,
**Enabled** and **Path**.  In the Quirks section there is no array, so no array attributes. Instead we just have individual settings.
In the schema language a rule applied to an array attribute is denoted by square brackets, **[Enabled]**, around the attribute name.
For a setting, leave off the brackets.

So to match the above xml in OCSanity's schema language you would write:

```
ACPI
:Add
 [Enabled]==yes " {$Path} is enabled":"!Hey, why did you disable {$Path}??"
:Quirks
 FadtEnableReset=no
```

Note that the attributes for that array element are available as variables that can be used in the output.

The leading space in front of `[Enabled]` is important.  Here are what the different leading characters do:

|  Char   | Action                           |
| ------- | -------------------------------- |
| **#**   | Comment - ignored                |
| **=**   | Output the rest of this line     |
| **:**   | Enter section                    |
| **::**  | Enter sub-section                |
| **$**   | Assign a variable: `$var=data`   |
| **\<space\>** | Define a rule to be run in current block group/section |
|         | Anything else defines a group    |

## Rules

A rule has this basic format:

```
 Setting=value " True message":"!False message"
```

That is, if the **Setting** is set to **value** then the **" True message"** is printed. Otherwise
the **"!False message"** is shown. Again here the leading character has a meaning:

|  Char   | Action                           |
| ------- | -------------------------------- |
| **\<space\>**   | Good (green)             |
| **-**   | Warning (yellow)                 |
| **!**   | Error (red)                      |
| **%**   | Info (blue)                      |
| **$**   | Assign a variable: `$var=data;`  |

For settings, if you leave the true/false messages out, the built-in ones will be shown.
For any rule you can override the built-in messages. For example:

```
UEFI
 ConnectDrivers=yes
```

This will generate a good (green) `**ConnectDrivers** = **Yes**` if the config.plist file being scanned has that setting enabled.
If they don't it generates warning (yellow) `**ConnectDrivers** = **No** but should normally be **Yes**`

You can override just the false message like this:

```
UEFI
 ConnectDrivers=yes :"!**{$setting}** = **{$value}** are you sure you don't want this enabled?"
```

Since we know the name of the setting in this case, we don't really need to use **{$setting}** there, but it is
just illustrating variable support in the messages. The \*\* is not part of it. That's just markdown for bold.

If a setting has multiple values in an array, but the array has no properties, like this:

```xml
    <key>Drivers</key>
    <array>
        <string>HFSPlus.efi</string>
        <string>ApfsDriverLoader.efi</string>
        <string>FwRuntimeServices.efi</string>
    </array>
```

You can check it using syntax like this:

```
 Drivers==VirtualSmc.efi "!**VirtualSmc.efi** was absorbed into Opencore under the quirk **AppleSmcIo**!"
```

That scans the array and if it finds **VirualSmc.efi** it will print that error message. There is a special wildcard case that looks like this:

```
 Drivers==* " **{$value}**"
```

That will match any array entries not matched by a previous rule. You will never get two messages on the same array element, so order is
important here. If you put this wildcard rule first, it will match each array entry and no other rules will trigger for this array.

There is also a special `count` rule that counts the number of elements in an array. Examples:

```
=[acpi]:https://khronokernel-2.gitbook.io/opencore-vanilla-desktop-guide/amd-config.plist/amd-config#acpi
ACPI
:Add
 count==0 "-You have no SSDT Patches. Please review the [ACPI Docs][acpi]"
```

Here we can also see how we can take advantage of the markdown format. We use a **=** line to define a markdown link which we can then use in the warning message.

You can of course also use **<** and **>**:

```
ACPI
:Patch
 count>15 "!You may have added the kernel patches in the wrong section. They should be in the Kernel section. Please review the [Kernel Docs][kernel]"
```

## Regular Expressions

You can also use full PHP-style PCRE regular expressions. The **~=** comparison operator denotes a regular expression comparison.

```
NVRAM
:Add
::4D1EDE05-38C7-4A6A-9CC6-4BCCA8B38C14
 UIScale~=01|02 " **{$setting}** = **{$value}**":" **{$setting}** = **{$value}** but it is usally set to **01** or **02**"
```

Here we are in a sub-section (denoted by the **::**) checking the UIScale setting. The scanner is type-aware, so it knows this
plist element is actually a binary data field and it is converted to hex internally for checks like this. So to check if it is set to
either **01** or **02** we can just check it against the simple **01|02** regex. If your expression has spaces in it, you can put it inside double quotes.

A regex can also be used to check if an attribute exists and is set to anything like this:

```
Kernel
:Patch
 [MatchOS]~=.+ "!You have used Clover patches, not OpenCore"
```

The **.+** is a regex that means at least 1 of any character.

## Advanced Variable Usage

It is possible to define variables both unconditionally at the top level but also conditionally inside a true/false message.
So we can do things like this:

```
 [BundlePath]==AppleALC.kext "$alcbootarg='-**{$setting}** = **{$value}** You need to add **alcid=N** here since you are using AppleALC.kext';":"$alcbootarg=;"
```

Read that one very carefully. It isn't actually outputting anything. If **AppleALC.kext** is present it will set the **$alcbootarg** variable to a string containing
a warning message. If it isn't there, the variable is set to an empty string. Now later on we might have something like this:

```
NVRAM
:Add
::7C436110-AB2A-4BBB-A880-FE41995C9F82
 boot-args~="^(?:(?!alcid).)*$" "{$alcbootarg}":""
 boot-args="-v keepsyms=1" " **{$setting}** = **{$value}** If you have a navi10 GPU add **agdpmod=pikera**":" **{$setting}** = **{$value}**"
```

Now we need to put on our regex thinking cap. That `^(?:(?!alcid).)*$"` regex checks to see if the word `alcid` appears anywhere in the **boot-args** setting.
If it does, the true message is printed which is just the contents of the **$alcbootarg** variable that we set earlier. So, what this does is it will print a warning
if **AppleALC.kext** is included, but **boot-args** does not include an **alcid** layout. Note that the false message is forced to empty with **:""**. If we didn't
do that, then the next **boot-args** rule below it might not trigger. Remember only one message per entry and we only want to warn about **alcid** if the situation
calls for that, otherwise if **boot-args** was left at the default setting we provide a helpful reminder to perhaps add a GPU flag.


## Installation

`composer install` then run `vendor/bin/phpunit` to run the unit tests and `vendor/bin/phan` to run a static analysis scan.

`./ocs -m -r rules/amd055.lst Sample.plist` to run a rule against a plist file on the command line with output in markdown format.

For a web install, point set your docroot to the htdocs directory and make sure you have PHP 7.x enabled.

You can see it running at https://opencore-dev.slowgeek.com
