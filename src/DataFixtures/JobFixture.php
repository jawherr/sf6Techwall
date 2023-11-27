<?php

namespace App\DataFixtures;

use App\Entity\Job;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class JobFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $data = [
            "Data scientist",
            "Statisticien",
            "Analyste cyber-sécurité",
            "Médecin ORL",
            "Echograohiste",
            "Mathématicien",
            "Ingénieur logiciel",
            "Analyste informatique",
            "Pathologiste de sicours / langage",
            "Actuaire",
            "Ergothrapeute",
            "Directeur des Ressource Humaines",
            "Hygiéniste dentaire"
        ];
        for($i=0;$i<count($data);$i++){
            $job = new Job();
            $job->setDesignation($data[$i]);
            $manager->persist($job);
        }
        $manager->flush();
    }
}
