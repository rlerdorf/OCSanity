<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OCSettingTest extends TestCase {
    protected static $pd;
    protected static $oc;

    public static function setUpBeforeClass(): void {
        self::$pd = new ParsedownExtra();
        self::$oc = new OpenCorePlist(__DIR__.'/Sample.plist');
    }

    private function applyRules(string $rules) {
        ob_start();
        self::$oc->applyRules(new Rules("",explode("\n", $rules)));
        $buf = ob_get_clean();
        // We now have parsedown-extra text in $buf
        return $buf;
    }

    public function testSimpleSetting(): void {
        $rules = <<< 'RULES'
        Booter
        :Quirks
         AvoidRuntimeDefrag=yes " Good":" Bad"

        RULES;

        $buf = $this->applyRules($rules);
        $this->assertStringContainsString('Good', $buf);
    }

    public function testSettingVars(): void {
        $rules = <<< 'RULES'
        Booter
        :Quirks
         AvoidRuntimeDefrag=yes " {$setting} Good {$value}":" Bad"

        RULES;

        $buf = $this->applyRules($rules);
        $this->assertStringContainsString('AvoidRuntimeDefrag Good Yes', $buf);
    }

    public function testSettingBinary(): void {
        $rules = <<< 'RULES'
        PlatformInfo
        :Generic
         ROM=112233000000 " {$setting} {$value}"

        RULES;

        $buf = $this->applyRules($rules);
        $this->assertStringContainsString('ROM 112233000000', $buf);
    }

    public function testSettingBinaryFalse(): void {
        $rules = <<< 'RULES'
        PlatformInfo
        :Generic
         ROM=123 :" {$setting} {$value}"

        RULES;

        $buf = $this->applyRules($rules);
        $this->assertStringContainsString('ROM 112233000000', $buf);
    }

    public function testSettingRegex(): void {
        $rules = <<< 'RULES'
        PlatformInfo
        :Generic
         ROM~="(123|112233000000)" " {$setting} {$value}"

        RULES;

        $buf = $this->applyRules($rules);
        $this->assertStringContainsString('ROM 112233000000', $buf);
    }

    public function testSettingMultiple(): void {
        $rules = <<< 'RULES'
        Kernel
        :Quirks
         XhciPortLimit=no

        UEFI
        :Input
         KeyForgetThreshold=5
         KeySwap=no
         PointerSupportMode=""
        :Protocols
         SanitiseClearScreen=yes

        RULES;

        $buf = $this->applyRules($rules);
        $this->assertStringContainsString('"fas good fa-check-circle">**XhciPortLimit** = **No**', $buf);
        $this->assertStringContainsString('"fas good fa-check-circle">**KeyForgetThreshold** = **5**', $buf);
        $this->assertStringContainsString('"fas good fa-check-circle">**KeySwap** = **No**', $buf);
        $this->assertStringContainsString('"fas warn fa-question-circle">**PointerSupportMode** = **ASUS** but should normally be **_-blank-_**', $buf);
        $this->assertStringContainsString('"fas warn fa-question-circle">**SanitiseClearScreen** is missing. Normally set to **Yes**', $buf);
    }

    public function testSettingNested(): void {
        $rules = <<< 'RULES'
        NVRAM
        :Add
        ::4D1EDE05-38C7-4A6A-9CC6-4BCCA8B38C14
         UIScale~=(01|02) " Good {$setting} = {$value}":" {$setting} = {$value} is usually set to 01 or 02"
        ::7C436110-AB2A-4BBB-A880-FE41995C9F82
         boot-args="-v keepsyms=1" " {$setting} = {$value} If you have a navi10 GPU add **agdpmod=pikera**":" {$setting} = {$value}"
         csr-active-config=00000000 " Good {$setting} = {$value}":" Bad {$setting} = {$value}"
         nvda_drv="" " {$setting} = 1 if you have a supported nvidia card":" {$setting} is {$value}"
         prev-lang:kbd=72752d52553a323532 "!{$setting} = {@value}. Unless you speak Russian, leave this blank":" {$setting} = {$value}"

        RULES;

        $buf = $this->applyRules($rules);
        $this->assertStringContainsString('Good UIScale = 01', $buf);
        $this->assertStringContainsString('boot-args = -v keepsyms=1', $buf);
        $this->assertStringContainsString('Good csr-active-config = 00000000', $buf);
        $this->assertStringContainsString('nvda_drv is 31', $buf);
        $this->assertStringContainsString('prev-lang:kbd = ru-RU:252. Unless', $buf);
    }

    public function testSettingVarConditional(): void {
        $rules = <<< 'RULES'
        Kernel
        :Add
         [BundlePath]==AppleALC.kext "$alc='-**{$setting}** = **{$value}** You need to add **alcid=N** here since you are using AppleALC.kext';":"$alc=;"
         [BundlePath]==* " **{$BundlePath}** make sure this Kext is in your **OC/Kexts** directory"

        NVRAM
        :Add
        ::7C436110-AB2A-4BBB-A880-FE41995C9F82
         boot-args~="^(?:(?!alcid).)*$" "{$alc}":""
         boot-args~="-v keepsyms=1" " {$setting} = {$value} If you have a navi10 GPU add **agdpmod=pikera**":" {$setting} = {$value}"

        RULES;

        $buf = $this->applyRules($rules);
        $this->assertStringContainsString('**AppleALC.kext** make sure', $buf);
        $this->assertStringContainsString('You need to add **alcid=N', $buf);
        $this->assertStringNotContainsString('pikera', $buf);
    }
}
