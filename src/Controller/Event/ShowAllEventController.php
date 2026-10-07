<?php

namespace App\Controller\Event;

use App\Repository\EventRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[AsController]
#[Route(path: '/events', name: 'all_events', methods: ['GET'])]
class ShowAllEventController
{
    public function __invoke(Environment $twig, EventRepository $event) : Response
    {
        return new Response($twig->render('Event/events.html.twig',[
            'events' => $event->findEventPublished()
        ]),Response::HTTP_OK);
    }
}
