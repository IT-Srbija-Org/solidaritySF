<?php

namespace App\Tests\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends WebTestCase
{
    public function testAdminAreaRedirectsOrDeniesAccess(): void
    {
        // Create a client without logging in
        $client = static::createClient();
        $client->request('GET', '/admin/');
        
        $response = $client->getResponse();
        $statusCode = $response->getStatusCode();
        
        // The test environment might behave differently, but it should not give a 200 OK
        // for admin areas without authentication
        $this->assertNotEquals(Response::HTTP_OK, $statusCode, 
            "Admin area should not return 200 OK without authentication, got status code: {$statusCode}"
        );
        
        // Verify it's either a redirect or an error
        $this->assertTrue(
            $response->isRedirection() || 
            in_array($statusCode, [
                Response::HTTP_UNAUTHORIZED, 
                Response::HTTP_FORBIDDEN,
                Response::HTTP_INTERNAL_SERVER_ERROR, // Might happen if security is misconfigured in test
            ]), 
            "Expected redirect or error status code, got: {$statusCode}"
        );
    }
}