<?php

namespace App\DataFixtures;

use App\Entity\Season;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SeasonFixtures extends Fixture implements DependentFixtureInterface
{
    public const SEASONS = [
        'Clever people know how to make money out of nothing.',
        'Tom peeped in the window and saw that Mary was still sleeping.',
        'In a paper published in Nature earlier this week, DeepMind revealed that a new version of AlphaGo (which they christened AlphaGo Zero) picked up Go from scratch, without studying any human games at all.',
        'In the second case, there will, of course, be difficulties.',
        'Plenty of great Italian places in NYC.',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (ProgramFixtures::PROGRAMS as $key => $programInfos) {
            foreach (self::SEASONS as $n => $seasonDescription) {
                $season = new Season();
                $season->setNumber($n + 1);
                $season->setYear($n + 2010);
                $season->setDescription($programInfos['title']
                    . '. ' . $seasonDescription);
                $season->setProgram($this->getReference('program_' . $key));
                $manager->persist($season);
                $this->addReference('season_' . $key . '_' . $n, $season);
            }
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            ProgramFixtures::class,
        ];
    }
}
