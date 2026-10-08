<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Repository\EventRepository;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment;

#[AsController]
#[Route(path: '/organizer', name: 'organizer', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[IsGranted( new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_ORGANISER")'))]
class OrganizerUserController
{
    public function __invoke(Environment $twig, EventRepository $event, #[CurrentUser] ?User $user = null): Response
    {
        if($user->getRoles()[0] === 'ROLE_ADMIN'){
            $events = $event->findAll();
        }else {$events = $event->findEventByOrganizer($user);}
        return new Response($twig->render('Event/events.html.twig',[
            'events' => $events
        ]));
    }
}
