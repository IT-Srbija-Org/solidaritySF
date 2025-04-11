<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RedirectControllerTest extends WebTestCase
{
    // Testing one URL is sufficient since all routes share the same controller method
    // and have identical behavior (301 redirect to homepage)
    public function testLegacyUrlRedirectsToHome(): void
    {
        $client = static::createClient();
        $client->request('GET', '/hvalaDonatoru');

        $this->assertResponseRedirects('/', Response::HTTP_MOVED_PERMANENTLY);
    }
}
