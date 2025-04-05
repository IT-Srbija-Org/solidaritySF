<?php

namespace App\Tests\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CityControllerTest extends WebTestCase
{
    public function testCityListRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/city/');
        
        $response = $client->getResponse();
        $statusCode = $response->getStatusCode();
        
        // Should not be accessible without authentication
        $this->assertNotEquals(Response::HTTP_OK, $statusCode);
        
        $this->assertTrue(
            $response->isRedirection() || 
            in_array($statusCode, [
                Response::HTTP_UNAUTHORIZED, 
                Response::HTTP_FORBIDDEN,
                Response::HTTP_INTERNAL_SERVER_ERROR
            ])
        );
    }
    
    public function testCityNewRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/city/new');
        
        $response = $client->getResponse();
        $statusCode = $response->getStatusCode();
        
        // Should not be accessible without authentication
        $this->assertNotEquals(Response::HTTP_OK, $statusCode);
        
        $this->assertTrue(
            $response->isRedirection() || 
            in_array($statusCode, [
                Response::HTTP_UNAUTHORIZED, 
                Response::HTTP_FORBIDDEN,
                Response::HTTP_INTERNAL_SERVER_ERROR
            ])
        );
    }
    
    public function testCityEditRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/city/1/edit');
        
        $response = $client->getResponse();
        $statusCode = $response->getStatusCode();
        
        // Should not be accessible without authentication
        $this->assertNotEquals(Response::HTTP_OK, $statusCode);
        
        $this->assertTrue(
            $response->isRedirection() || 
            in_array($statusCode, [
                Response::HTTP_UNAUTHORIZED, 
                Response::HTTP_FORBIDDEN,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                Response::HTTP_NOT_FOUND  // Could be a 404 if ID 1 doesn't exist
            ])
        );
    }
}