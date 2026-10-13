<?php

namespace App\Controller\Event;

use App\Entity\Registration;
use App\Entity\User;
use App\Enum\EventStatus;
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
use function PHPUnit\Framework\isEmpty;

#[AsController]
#[Route(path: '/events/{slug}/register', name: 'event_register', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class RegisterEventController
{
    public function __invoke(string $slug, Environment $twig, EventRepository $eventRepository, RegistrationRepository $registration, #[CurrentUser] ?User $user = null, UrlGeneratorInterface $urlGenerator): Response
    {
        $event = $eventRepository->findOneBySlug($slug);
        $registered = $registration->findOneBy(['user' => $user, 'event' => $event]);
        if (!$event || $event->getStatus() !== EventStatus::Published) {
            throw $this->createNotFoundException();
        }
        if ($registered !== null && $registered->getStatus() !== RegistrationStatus::Confirmed) {
            $registered->setStatus(RegistrationStatus::Confirmed);
            $registration->persistandsave($registered);
            return new RedirectResponse($urlGenerator->generate('event_list_registered'));
        }elseif($registered === null){
            $register = new Registration();
            $register->setEvent($event);
            $register->setUser($user);
            $registration->persistandsave($register);
            return new RedirectResponse($urlGenerator->generate('event_list_registered'));
        }else{
            throw $this->createNotFoundException();
        }
    }
}
