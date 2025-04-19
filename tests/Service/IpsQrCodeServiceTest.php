<?php

namespace App\Tests\Service;

use App\Service\IpsQrCodeService;
use PHPUnit\Framework\TestCase;

// Adapted from: https://github.com/ArtBIT/ips-qr-code/blob/master/lib/ips.test.js

class IpsQrCodeServiceTest extends TestCase
{
    private IpsQrCodeService $service;

    protected function setUp(): void
    {
        $this->service = new IpsQrCodeService();
    }

    public function testThrowsOnMissingRequiredArguments(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->createIpsQrString([]);
    }

    public function testWorksWithRequiredArgumentsAndDefaults(): void
    {
        $args = [
            'bankAccountNumber' => '123456789012345611',
            'payeeName' => 'JEST Ltd., Test',
            'amount' => '1295,',
            'payerName' => 'Test Payer',
            'paymentPurpose' => 'Test Purpose',
        ];
        $expected = 'K:PR|V:01|C:1|R:123456789012345611|N:JEST Ltd., Test|I:RSD1295,|P:Test Payer|S:Test Purpose';
        $this->assertSame($expected, $this->service->createIpsQrString($args));
    }

    public function testWorksWithAllArgumentsProvided(): void
    {
        $args = [
            'identificationCode' => 'PR',
            'version' => '01',
            'characterSet' => '1',
            'bankAccountNumber' => '123456789012345611',
            'payeeName' => 'JEST Ltd., Test',
            'amount' => '1295,',
            'payerName' => 'Test Payer',
            'paymentCode' => '123',
            'paymentPurpose' => 'Testing',
        ];
        $expected = 'K:PR|V:01|C:1|R:123456789012345611|N:JEST Ltd., Test|I:RSD1295,|P:Test Payer|SF:123|S:Testing';
        $this->assertSame($expected, $this->service->createIpsQrString($args));
    }

    public function testWorksWithReferenceCode(): void
    {
        $args = [
            'bankAccountNumber' => '123456789012345611',
            'payeeName' => 'JEST Ltd., Test',
            'amount' => '1295,',
            'payerName' => 'Test Payer',
            'paymentPurpose' => 'Test Purpose',
            'referenceCode' => '972012345',
        ];
        $expected = 'K:PR|V:01|C:1|R:123456789012345611|N:JEST Ltd., Test|I:RSD1295,|P:Test Payer|S:Test Purpose|RO:972012345';
        $this->assertSame($expected, $this->service->createIpsQrString($args));
    }
}
