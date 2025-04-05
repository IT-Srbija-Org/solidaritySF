<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/logovanje');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
        $this->assertSelectorExists('button[type="submit"]');
    }

    public function testForgotPasswordPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/zaboravljena-lozinka');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="reset_password"]');
        $this->assertSelectorExists('input[id="reset_password_email"]');
    }
}