<?php

namespace App\DataFixtures;

use App\Entity\Episode;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    public const EPISODES = [
        'I like cucumbers and olives.',
        'Her water bottle had a built-in filtration system.',
        'That\'s a bit of an exaggeration.',
        'Why on earth would you wear a tie like that?',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (ProgramFixtures::PROGRAMS as $key => $programInfos) {
            foreach (SeasonFixtures::SEASONS as $n => $seasonDescription) {
                foreach (self::EPISODES as $i => $episodeName) {
                    $episode = new Episode();
                    $episode->setTitle($episodeName);
                    $episode->setSynopsis('Always program as if the person who will maintain your code is a maniac serial killer who knows where you live.');
                    $episode->setNumber($i + 1);
                    $episode->setSeason($this->getReference('season_' . $key . '_' . $n));
                    $manager->persist($episode);
                    $this->addReference('episode_' . $key . '_' . $n . '_' . $i, $episode);
                }
            }
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            SeasonFixtures::class,
            ProgramFixtures::class,
        ];
    }
}
