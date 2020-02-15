<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OCSettingTest extends TestCase {
    protected static $pd;
    protected static $oc;

    public static function setUpBeforeClass(): void {
        self::$pd = new ParsedownExtra();
        self::$oc = new OpenCorePlist('./Sample.plist');
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
         ROM~=(123|112233000000) " {$setting} {$value}"

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
        $this->assertStringContainsString('"good">**XhciPortLimit** = **No**', $buf);
        $this->assertStringContainsString('"good">**KeyForgetThreshold** = **5**', $buf);
        $this->assertStringContainsString('"good">**KeySwap** = **No**', $buf);
        $this->assertStringContainsString('"warn">**PointerSupportMode** should normally be **<blank>**', $buf);
        $this->assertStringContainsString('"warn">**SanitiseClearScreen** is missing. Normally set to **Yes**', $buf);
    }
}
