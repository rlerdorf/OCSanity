<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OCAttributeTest extends TestCase {
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

    public function testSimpleAttribute(): void {
        $rules = <<< 'RULES'
        ACPI
        :Add
         [Path]==SSDT-1.aml " Good"

        RULES;

        $buf = $this->applyRules($rules);
        $this->assertStringContainsString('Good', $buf);
    }

    public function testAttributeCount(): void {
        $rules = <<< 'RULES'
        ACPI
        :Add
         count==12 " You have 12"
         count>10 " You have more than 10"
         count<8 " You have less than 8":"!You do not have less than 8"
         [Path]==SSDT-1.aml " Good"

        RULES;

        $buf = $this->applyRules($rules);
        $this->assertStringContainsString('You have 12', $buf);
        $this->assertStringContainsString('You have more than 10', $buf);
        $this->assertStringContainsString('You do not have less than 8', $buf);
        $this->assertStringContainsString('Good', $buf);
    }

    public function testAttributeWildcard(): void {
        $rules = <<< 'RULES'
        ACPI
        :Add
         [Path]==* " {$Path} Ok"

        RULES;

        $buf = $this->applyRules($rules);
        $this->assertStringContainsString('DSDT.aml Ok', $buf);
        $this->assertStringContainsString('SSDT-1.aml Ok', $buf);
        $this->assertStringContainsString('SSDT-PLUG.aml Ok', $buf);
        $this->assertStringContainsString('SSDT-SBUS-MCHC.aml Ok', $buf);
        $this->assertStringContainsString('SSDT-PNLF.aml Ok', $buf);
        $this->assertStringContainsString('SSDT-EC.aml Ok', $buf);
        $this->assertStringContainsString('SSDT-EC-USBX.aml Ok', $buf);
        $this->assertStringContainsString('SSDT-EHCx_OFF.aml Ok', $buf);
        $this->assertStringContainsString('SSDT-AWAC.aml Ok', $buf);
        $this->assertStringContainsString('SSDT-RTC0.aml Ok', $buf);
        $this->assertStringContainsString('SSDT-ALS0.aml Ok', $buf);
        $this->assertStringContainsString('SSDT-PMC.aml Ok', $buf);
    }

    public function testAttributeOR(): void {
        $rules = <<< 'RULES'
        ACPI
        :Add
         [Path]==SSDT-EC-USBX.aml|SSDT-EC.aml " {$Path} Ok":"!{$Path} Bad"
         [Path]==SSDT-EC-USBX.aml|SSDT-EC.aml " {$Path} Ok":"!{$Path} Bad"
         [Path]==SSDT-EC-USBX.aml|SSDT-EC.aml " {$Path} Ok":"!Bad1"
         [Path]==SSDT-EC-USBX-BAR.aml|SSDT-EC-FOO.aml|SSDT-EC-NOTHERE.aml " {$Path} Ok":"!Bad2"

        RULES;

        $buf = $this->applyRules($rules);
        $this->assertStringContainsString('SSDT-EC-USBX.aml Ok', $buf);
        $this->assertStringContainsString('SSDT-EC.aml Ok', $buf);
        $this->assertStringContainsString('Bad1', $buf);
        $this->assertStringContainsString('Bad2', $buf);
    }

    public function testAttributeAnd(): void {
        $rules = <<< 'RULES'
        ACPI
        :Add
         [Path]==SSDT-EC-USBX.aml&SSDT-EC.aml " {$Path} Ok":"!{$Path} Bad"
         [Path]==SSDT-EC-USBX.aml&SSDT-EC.aml " {$Path} Ok":"!{$Path} Bad"
         [Path]==SSDT-EC-USBX.aml&SSDT-EC.aml " {$Path} Ok":"!Bad1"
         [Path]==SSDT-EC-USBX-BAR.aml&SSDT-EC-FOO.aml&SSDT-EC-NOTHERE.aml " {$Path} Ok":"!Bad2"

        RULES;

        $buf = $this->applyRules($rules);
        $this->assertStringContainsString('SSDT-EC-USBX.aml Ok', $buf);
        $this->assertStringContainsString('SSDT-EC.aml Bad', $buf);
        $this->assertStringContainsString('Bad1', $buf);
        $this->assertStringContainsString('Bad2', $buf);
    }

    public function testAttributeRegex(): void {
        $rules = <<< 'RULES'
        ACPI
        :Add
         [Path]~=.dsl$ " {$Path} oops":" Good no dsl files here"
         [Path]~=.aml$ " {$Path} good aml":"!no aml files here?"

        RULES;

        $buf = $this->applyRules($rules);
        $this->assertStringContainsString('Good no dsl files here', $buf);
        $this->assertStringContainsString('SSDT-PMC.aml good aml', $buf);
    }
}
