<?php

namespace App\Tests\Repository;

use App\Entity\SchoolType;
use App\Repository\SchoolTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SchoolTypeRepositoryTest extends KernelTestCase
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
        
        // Create test school types
        $elementary = new SchoolType();
        $elementary->setName('Elementary School');
        
        $highSchool = new SchoolType();
        $highSchool->setName('High School');
        
        $this->entityManager->persist($elementary);
        $this->entityManager->persist($highSchool);
        $this->entityManager->flush();
    }
    
    public function testFindAll(): void
    {
        /** @var SchoolTypeRepository $schoolTypeRepository */
        $schoolTypeRepository = $this->entityManager->getRepository(SchoolType::class);
        
        // Test findAll method
        $result = $schoolTypeRepository->findAll();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        
        // Test findBy method with criteria
        $result = $schoolTypeRepository->findBy(['name' => 'Elementary School']);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Elementary School', $result[0]->getName());
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