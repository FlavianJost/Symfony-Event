<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Repository\RegistrationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment;

#[AsController]
#[Route(path: '/events-registered', name: 'event_list_registered', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class ListRegistrationController
{
    public function __invoke(Environment $twig, RegistrationRepository $registrationRepository, #[CurrentUser] ?User $user = null) : Response
    {
        $registrations = [];$events = [];
        foreach ($registrationRepository->findRegistrationByUser($user) as $registration){
            $events[$registration->getEvent()->getId()] = $registration->getEvent();
            $registrations[$registration->getEvent()->getId()] = $registration;
        }
        return new Response($twig->render('Event/events.html.twig', [
            'events' => $events ?? [],
            'registrations' => $registrations
        ]), Response::HTTP_OK);
    }
}
