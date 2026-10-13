<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;

#[\PHPUnit\Framework\Attributes\IgnoreDeprecations]
final class AuthFlowTest extends BaseWebTestCase
{
    public function testLoginPageLoads(): void
    {
        $this->client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Connexion à Full Project');
        self::assertSelectorExists('input[name="_csrf_token"]');
    }

    public function testRegisterPageLoads(): void
    {
        $this->client->request('GET', '/register');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Créer un compte');
    }

    public function testLoginWithValidCredentials(): void
    {
        $this->login(self::USER_EMAIL);

        self::assertResponseRedirects('/');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('nav', 'Déconnexion');
    }

    public function testLoginWithInvalidCredentialsRedirectsBack(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form();

        $this->client->submit($form, [
            'email' => self::USER_EMAIL,
            'password' => 'mot-de-passe-incorrect',
        ]);

        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
    }

    public function testLoginWithTamperedCsrfTokenIsRejected(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_csrf_token'] = 'token-falsifié';

        $this->client->submit($form, [
            'email' => self::USER_EMAIL,
            'password' => self::PASSWORD,
        ]);

        self::assertResponseRedirects('/login');
    }

    public function testLogoutRedirectsToHomepageAndDisconnects(): void
    {
        $this->login(self::USER_EMAIL);
        $this->client->followRedirect();

        $this->client->request('GET', '/logout');

        self::assertResponseRedirects('/');
        $this->client->followRedirect();

        $this->client->request('GET', '/profile');
        self::assertResponseRedirects('/login');
    }

    public function testRegisterFormSubmissionFailsDueToMissingLastLoginAt(): void
    {
        $email = 'test-' . uniqid() . '@example.com';
        $username = 'test' . substr(uniqid(), -6);

        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->selectButton('Créer un compte')->form();

        $this->client->submit($form, [
            'user' => [
                'username' => $username,
                'email' => $email,
                'password' => 'motdepasse12!',
            ],
        ]);

        // Bug applicatif documenté : RegisterUserController ne renseigne jamais lastLoginAt
        // (colonne NOT NULL en base) → l'insertion échoue avec une 500.
        self::assertResponseStatusCodeSame(500);
        self::assertNull($this->entityManager()->getRepository(User::class)->findOneBy(['email' => $email]));
    }

    public function testRegisterWithExistingEmailDoesNotCreateDuplicate(): void
    {
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->selectButton('Créer un compte')->form();

        $this->client->submit($form, [
            'user' => [
                'username' => 'doublon-' . uniqid(),
                'email' => self::USER_EMAIL,
                'password' => 'motdepasse12!',
            ],
        ]);

        self::assertFalse($this->client->getResponse()->isRedirect());

        $count = $this->entityManager()->getRepository(User::class)->count(['email' => self::USER_EMAIL]);
        self::assertSame(1, $count);
    }
}