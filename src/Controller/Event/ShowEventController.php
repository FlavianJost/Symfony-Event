<?php

namespace App\Controller\Event;

use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Repository\RegistrationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Twig\Environment;

#[AsController]
#[Route(path: '/events/{slug}', name: 'event_show', methods: ['GET'])]
class ShowEventController
{
    public function __invoke(string $slug, Environment $twig, EventRepository $event, RegistrationRepository $registration, #[CurrentUser] ?User $user) : Response
    {
        $found = $event->findOneBy(['slug' => $slug]);

        if (!$found instanceof Event) {
            throw new NotFoundHttpException(sprintf('Event "%s" not found', $slug));
        }



        return new Response($twig->render('Event/show.html.twig',[
            'event' => $found,
            'registrationsCount' => $registration->countConfirmedByEvent($found),
            'registered' => in_array($found, $registration->findEventsByUser($user), true)
        ]),Response::HTTP_OK);
    }
}
