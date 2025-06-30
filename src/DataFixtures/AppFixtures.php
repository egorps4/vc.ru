<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $userNames = ['Адам Дженсен', 'Granger', 'Вадим', 'Габен', 'Гервант', 'Егор', 'Анжелика', 'Натан Драке', 'Думгай', 'Снейк'];

        $users = [];
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setName($userNames[$i]);
            $manager->persist($user);
            $users[] = $user;
        }

        $gameTopics = [
            'Обзор новой игры {game}',
            'Розыгрыш игры {game}',
            'Вы НЕ поняли {game}',
            'Почему {game} плохая игра',
            'Я прошел {game} и это шедевр',
        ];
        $games = ['Cyberpunk 2077', 'Deus Ex', 'The Witcher 3', 'Baldur\'s Gate 3', 'Doom'];

        for ($i = 0; $i < 5000; $i++) {
            $post = new Post();
            $game = $faker->randomElement($games);
            $topic = str_replace('{game}', $game, $faker->randomElement($gameTopics));
            $post->setTitle($topic);
            $post->setContent($faker->paragraphs(3, true));
            $post->setHotness($faker->randomFloat(2, 0, 100));
            $post->setCreatedAt($faker->dateTimeBetween('-1 year', 'now'));
            $post->setAuthor($faker->randomElement($users));
            $post->setViewCount($faker->numberBetween(0, 1500));
            $manager->persist($post);
        }

        $manager->flush();
    }
}
