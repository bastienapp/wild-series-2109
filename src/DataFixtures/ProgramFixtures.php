<?php

namespace App\DataFixtures;

use App\Entity\Program;
use App\Service\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{
    public const PROGRAMS = [
        [
            'title' => 'Eventually the experiment succeeded',
            'synopsis' => 'Tom calculated that he had given Mary over 34,000 dollars in the past six months.',
            'country' => 'USA',
            'year' => 2012,
            'category' => 'category_0',
        ],
        [
            'title' => 'She kept on talking',
            'synopsis' => 'If everybody just added perfect sentences, this project would never be that interactive and interesting.',
            'country' => 'France',
            'year' => 2008,
            'category' => 'category_0',
        ],
        [
            'title' => 'Quit picking on her',
            'synopsis' => 'The bigger words he used, the harder it was to find anything inside of them.',
            'country' => 'Spain',
            'year' => 1987,
            'category' => 'category_1',
        ],
        [
            'title' => 'The oracle was fulfilled',
            'synopsis' => 'May God give those who know me ten times more than what they give me.',
            'country' => 'Italy',
            'year' => 2020,
            'category' => 'category_2',
        ],
        [
            'title' => 'Let\'s hold that thought',
            'synopsis' => 'We all know what we owe to our country. The tax department lets us know.',
            'country' => 'USA',
            'year' => 2016,
            'category' => 'category_3',
        ],
    ];

    private Slugify $slugify;

    public function __construct(Slugify $slugify)
    {
        $this->slugify = $slugify;
    }

    public function load(ObjectManager $manager)
    {
        $user = $this->getReference('user_tacos');
        foreach (SELF::PROGRAMS as $key => $programInfos) {
            $program = new Program();
            $program->setTitle($programInfos['title']);
            $program->setSynopsis($programInfos['synopsis']);
            $program->setCountry($programInfos['country']);
            $program->setYear($programInfos['year']);
            $program->setSlug($this->slugify->generate($programInfos['title']));
            $program->setCategory($this->getReference($programInfos['category']));
            for ($i=0; $i < count(ActorFixtures::ACTORS); $i++) {
                $program->addActor($this->getReference('actor_' . $i));
            }
            $program->setOwner($user);
            $manager->persist($program);
            $this->addReference('program_' . $key, $program);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CategoryFixtures::class,
            ActorFixtures::class,
            UserFixtures::class,
        ];
    }
}
