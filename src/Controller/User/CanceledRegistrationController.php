<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Enum\RegistrationStatus;
use App\Repository\EventRepository;
use App\Repository\RegistrationRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment;

#[AsController]
#[Route(path: '/events/{slug}/cancel_registered', name: 'event_cancel_registered', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class CanceledRegistrationController
{
    public function __invoke(string $slug, Environment $twig, EventRepository $event, RegistrationRepository $registration, #[CurrentUser] ?User $user = null, UrlGeneratorInterface $urlGenerator): Response
    {
        $event = $event->findOneBySlug($slug);
        $registered = $registration->findOneBy(['user' => $user, 'event' => $event]);
        if(!$event && !$registered){
            throw new \Exception('Event or registration not found');
        }
        $registered->setStatus(RegistrationStatus::Cancelled);
        $registration->save($registered);
        return new RedirectResponse($urlGenerator->generate('event_show', ['slug' => $slug]));
    }
}
