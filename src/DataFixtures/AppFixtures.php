<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserView;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $userFirstNames = [
            'Адам',
            'Granger',
            'Дум',
            'Гейб',
            'Геральт',
            'Егор',
            'Лара',
            'Натан',
            'Джей Си',
            'Солид',
            'Хорус',
            'Панам',
            'Трисс'
        ];

        $userLastNames = [
            'Дженсен',
            'Сосиска',
            'Гай',
            'Ньюэлл',
            'из Ривии',
            'Игнатьев',
            'Крофт',
            'Драке',
            'Дентон',
            'Снейк',
            'Лупрекаль',
            'Палмер',
            'Меригольд'
        ];

        $users = [];
        for ($i = 0; $i < 100; $i++) {
            $user = new User();
            $user->setName($faker->randomElement($userFirstNames) . ' ' . $faker->randomElement($userLastNames));
            $manager->persist($user);
            $users[] = $user;
        }

        $manager->flush();

        $gameTopics = [
            'Обзор новой игры {game}',
            'Розыгрыш игры {game}',
            'Вы НЕ поняли {game}',
            'Почему {game} плохая игра',
            'Я прошел {game} и это шедевр',
            'Утечка: {game} выйдет до конца текущего года',
            '{game} получила патч первого дня на 1000 ГБ',
            '{game} не повторит ошибок предшественницы - игра не выйдет'
        ];
        $games = ['Cyberpunk 2078', 'Deus Ex Icarus', 'The Witcher 4', 'Baldur\'s Gate 4', 'Doom final', 'Metal Gear Solid 6', 'Half-Life 3'];

        for ($i = 0; $i < 4000; $i++) {
            $post = new Post();
            $game = $faker->randomElement($games);
            $topic = str_replace('{game}', $game, $faker->randomElement($gameTopics));
            $post->setTitle($topic);
            $post->setContent($faker->paragraphs(1, true));
            $post->setHotness($faker->randomFloat(2, 0, 100));
            $post->setCreatedAt($faker->dateTimeBetween('-1 year', 'now'));
            $post->setAuthor($faker->randomElement($users));
            $post->setViewCount($faker->numberBetween(0, 1500));
            $manager->persist($post);

            $userView = new UserView();
            $userView->setPost($post);
            $userView->setUser(($faker->randomElement($users)));
            $manager->persist($userView);
        }

        $manager->flush();
    }
}
