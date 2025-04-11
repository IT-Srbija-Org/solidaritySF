<?php

namespace App\DataFixtures;

use App\DataFixtures\Data\Schools;
use App\Entity\City;
use App\Entity\School;
use App\Entity\SchoolType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class SchoolFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    private function determineSchoolType(string $schoolName): string
    {
        if (str_contains($schoolName, 'Osnovna škola')) {
            return 'Osnovna škola';
        }
        if (str_contains($schoolName, 'Gimnazija')) {
            return 'Gimnazija';
        }

        return 'Srednja stručna škola';
    }

    public function load(ObjectManager $manager): void
    {
        // Get all cities and school types upfront to reduce DB queries
        $cities = $this->entityManager->getRepository(City::class)->findAll();
        $cityNames = array_map(fn ($city) => $city->getName(), $cities);
        $cityMap = array_combine($cityNames, $cities);

        $schoolTypes = $this->entityManager->getRepository(SchoolType::class)->findAll();
        $schoolTypeNames = array_map(fn ($type) => $type->getName(), $schoolTypes);
        $schoolTypeMap = array_combine($schoolTypeNames, $schoolTypes);

        // Get schools data
        $schoolsData = Schools::getSchoolsMap();

        foreach ($schoolsData as $cityName => $schools) {
            if (!isset($cityMap[$cityName])) {
                continue;
            }

            // Get city entity from the preloaded data
            $city = $cityMap[$cityName];

            foreach ($schools as $schoolName) {
                // Create and configure the School entity
                $school = new School();
                $school->setName($schoolName);
                $school->setCity($city);

                // Determine school type
                $typeName = $this->determineSchoolType($schoolName);

                // Check if the school type exists
                if (!isset($schoolTypeMap[$typeName])) {
                    continue;
                }

                // Get school type entity from the preloaded data
                $type = $schoolTypeMap[$typeName];
                $school->setType($type);

                // Persist the school entity
                $manager->persist($school);
            }
        }

        // Flush all changes to the database
        $manager->flush();
    }

    /**
     * @return int[]
     */
    public static function getGroups(): array
    {
        return [2];
    }
}
