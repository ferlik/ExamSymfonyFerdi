<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    // ...

    public function testVisitingWhileLoggedIn()
    {
        $client = static::createClient();


        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('ferlik@hotmail.fr');

        $client->loginUser($testUser);

        // user is now logged in, so you can test protected resources
        $client->request('GET', '/admin');
        $this->assertResponseIsSuccessful();
    }
}
