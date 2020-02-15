<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OCGroupTest extends TestCase {
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

    public function testGroupStructure(): void {
        $rules = <<< 'RULES'
        ACPI
        Booter
        DeviceProperties
        Kernel
        Misc
        NVRAM
        PlatformInfo
        UEFI
        RULES;    

        $buf = $this->applyRules($rules);
        $this->assertNotEmpty($buf);
        $this->assertStringContainsString('## ACPI', $buf);
        $this->assertStringContainsString('## Booter', $buf);
        $this->assertStringContainsString('## DeviceProperties', $buf);
        $this->assertStringContainsString('## Kernel', $buf);
        $this->assertStringContainsString('## Misc', $buf);
        $this->assertStringContainsString('## NVRAM', $buf);
        $this->assertStringContainsString('## PlatformInfo', $buf);
        $this->assertStringContainsString('## UEFI', $buf);
    }

    public function testMissingGroup(): void {
        $rules = <<< 'RULES'
        ACPI
        Booter
        DeviceProperties
        Kernel
        Misc
        NVRAM
        PlatformInfo
        UEFI
        Missing
        RULES;    

        $buf = $this->applyRules($rules);
        $this->assertStringContainsString('*Missing* group is missing', $buf);
    }

    public function testExtraGroup(): void {
        $rules = <<< 'RULES'
        ACPI
        Booter
        DeviceProperties
        Kernel
        Misc
        NVRAM
        PlatformInfo
        RULES;    

        $buf = $this->applyRules($rules);
        $this->assertStringContainsString('*UEFI* this shouldn\'t be here', $buf);
    }
}
