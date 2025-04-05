<?php

namespace App\Tests\Repository;

use App\Entity\City;
use App\Entity\School;
use App\Entity\SchoolType;
use App\Repository\SchoolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SchoolRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?City $belgrade;
    private ?City $noviSad;
    private ?SchoolType $elementary;
    private ?SchoolType $highSchool;
    
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
        $this->belgrade = new City();
        $this->belgrade->setName('Belgrade');
        
        $this->noviSad = new City();
        $this->noviSad->setName('Novi Sad');
        
        // Create test school types
        $this->elementary = new SchoolType();
        $this->elementary->setName('Elementary School');
        
        $this->highSchool = new SchoolType();
        $this->highSchool->setName('High School');
        
        // Persist cities and school types
        $this->entityManager->persist($this->belgrade);
        $this->entityManager->persist($this->noviSad);
        $this->entityManager->persist($this->elementary);
        $this->entityManager->persist($this->highSchool);
        $this->entityManager->flush();
        
        // Create test schools
        $school1 = new School();
        $school1->setName('First Belgrade Elementary');
        $school1->setCity($this->belgrade);
        $school1->setType($this->elementary);
        
        $school2 = new School();
        $school2->setName('Belgrade High School');
        $school2->setCity($this->belgrade);
        $school2->setType($this->highSchool);
        
        $school3 = new School();
        $school3->setName('Novi Sad Elementary');
        $school3->setCity($this->noviSad);
        $school3->setType($this->elementary);
        
        // Persist schools
        $this->entityManager->persist($school1);
        $this->entityManager->persist($school2);
        $this->entityManager->persist($school3);
        $this->entityManager->flush();
    }
    
    public function testSearchMethod(): void
    {
        /** @var SchoolRepository $schoolRepository */
        $schoolRepository = $this->entityManager->getRepository(School::class);
        
        // Test empty search (should return all schools)
        $result = $schoolRepository->search([]);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('current_page', $result);
        $this->assertArrayHasKey('total_pages', $result);
        
        // Test the total count is 3 (our test schools)
        $this->assertEquals(3, $result['total']);
        
        // Test search with name criteria
        $result = $schoolRepository->search(['name' => 'Elementary']);
        $this->assertEquals(2, count($result['items']));
        
        // Test search with city criteria
        $result = $schoolRepository->search(['city' => $this->belgrade]);
        $this->assertEquals(2, count($result['items']));
        
        // Test search with type criteria
        $result = $schoolRepository->search(['type' => $this->elementary]);
        $this->assertEquals(2, count($result['items']));
        
        // Test combined search criteria
        $result = $schoolRepository->search([
            'name' => 'Belgrade',
            'city' => $this->belgrade,
            'type' => $this->highSchool
        ]);
        $this->assertEquals(1, count($result['items']));
        $this->assertEquals('Belgrade High School', $result['items'][0]->getName());
        
        // Test pagination
        $result = $schoolRepository->search([], 1, 2); // Page 1, limit 2
        $this->assertEquals(2, count($result['items'])); // 2 items per page
        $this->assertEquals(3, $result['total']); // 3 schools total
        $this->assertEquals(1, $result['current_page']); // Current page is 1
        $this->assertEquals(2, $result['total_pages']); // 2 pages total (3 items with 2 per page)
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