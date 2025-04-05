<?php

namespace App\Tests\Repository;

use App\Entity\City;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CityRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
            
        // Create schema for SQLite in-memory database
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        
        try {
            $schemaTool->createSchema($metadata);
        } catch (\Exception $e) {
            // Schema might already exist
        }
        
        // Create test cities
        $belgrade = new City();
        $belgrade->setName('Belgrade');
        
        $noviSad = new City();
        $noviSad->setName('Novi Sad');
        
        $nis = new City();
        $nis->setName('Nis');
        
        $this->entityManager->persist($belgrade);
        $this->entityManager->persist($noviSad);
        $this->entityManager->persist($nis);
        $this->entityManager->flush();
    }
    
    public function testSearchMethod(): void
    {
        /** @var CityRepository $cityRepository */
        $cityRepository = $this->entityManager->getRepository(City::class);
        
        // Test empty search (should return all cities)
        $result = $cityRepository->search([]);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('current_page', $result);
        $this->assertArrayHasKey('total_pages', $result);
        
        // Test the total count is 3 (our test cities)
        $this->assertEquals(3, $result['total']);
        
        // Test search with name criteria
        $result = $cityRepository->search(['name' => 'Novi']);
        $this->assertEquals(1, count($result['items']));
        $this->assertEquals('Novi Sad', $result['items'][0]->getName());
        
        // Test pagination
        $result = $cityRepository->search([], 1, 2); // Page 1, limit 2
        $this->assertEquals(2, count($result['items'])); // 2 items per page
        $this->assertEquals(3, $result['total']); // 3 cities total
        $this->assertEquals(1, $result['current_page']); // Current page is 1
        $this->assertEquals(2, $result['total_pages']); // 2 pages total (3 items with 2 per page)
        
        // Test second page
        $result = $cityRepository->search([], 2, 2); // Page 2, limit 2
        $this->assertEquals(1, count($result['items'])); // Only 1 item on second page
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up the entity manager to avoid memory leaks
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }
}