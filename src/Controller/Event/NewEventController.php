<?php

namespace App\Controller\Event;

use App\Entity\Event;
use App\Entity\User;
use App\Form\Event\EventType;
use App\Repository\EventRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment;

#[AsController]
#[Route(path: '/events/new', name: 'event_new', methods: ['GET', 'POST'])]
class NewEventController
{
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function __invoke(Request $request, Environment $twig, FormFactoryInterface $formFactory, EventRepository $eventRepository, UrlGeneratorInterface $urlGenerator, #[CurrentUser] ?User $user = null): Response
    {
        $roles = $user->getRoles();

        if (!in_array('ROLE_ORGANISER', $roles, true) && !in_array('ROLE_ADMIN', $roles, true)) {
            throw new AccessDeniedHttpException('Vous devez être connecté avec un compte organisateur ou administrateur pour créer un événement.');
        }

        $event = new Event();
        $event->setOrganizer($user);

        $form = $formFactory->create(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventRepository->saveandpersist($event);

            return new RedirectResponse($urlGenerator->generate('event_show', [
                'slug' => $event->getSlug(),
            ]));
        }

        return new Response($twig->render('Event/new.html.twig', [
            'form' => $form->createView(),
        ]), Response::HTTP_OK);
    }
}
