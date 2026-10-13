<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterUserControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/events');

        self::assertResponseIsSuccessful();
        //self::assertSelectorTextContains('h1', 'Créer un compte');
    }
}
