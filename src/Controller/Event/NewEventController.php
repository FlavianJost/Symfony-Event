<?php

namespace App\Controller\Event;

use App\Entity\Event;
use App\Entity\User;
use App\Form\Event\EventType;
use App\Repository\EventRepository;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Twig\Environment;

#[AsController]
#[Route(path: '/events/new', name: 'event_new', methods: ['GET', 'POST'])]
class NewEventController
{
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted( new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_ORGANISER")'))]
    public function __invoke(Request $request, Environment $twig, FormFactoryInterface $formFactory, EventRepository $eventRepository, UrlGeneratorInterface $urlGenerator, #[CurrentUser] ?User $user = null, SluggerInterface $slugger): Response
    {
        $event = new Event();
        $event->setSlug("none");
        $form = $formFactory->create(EventType::class, $event);
        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $event = $form->getData();
                $slug = strtolower($slugger->slug($event->getTitle())->toString());
                $event->setSlug($slug);
                $event->setOrganizer($user);
                $eventRepository->saveandpersist($event);
                return new RedirectResponse($urlGenerator->generate('event_show', [
                    'slug' => $slug,
                ]));
            }
        }catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return new Response($twig->render('Event/form.html.twig', [
            'form' => $form->createView(),
        ]), Response::HTTP_OK);
    }
}
