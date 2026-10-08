<?php

namespace App\Controller\Event;

use App\Entity\Event;
use App\Entity\User;
use App\Form\Event\EventType;
use App\Repository\EventRepository;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Twig\Environment;

#[AsController]
#[Route(path: '/events/{slug}/edit', name: 'edit_events', methods: ['GET', 'POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[IsGranted( new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_ORGANISER")'))]
class EditEventController
{
    public function __invoke(string $slug, Request $request, Environment $twig, EventRepository $eventRepository, FormFactoryInterface $formFactory, UrlGeneratorInterface $urlGenerator, #[CurrentUser] ?User $user = null, SluggerInterface $slugger) : Response
    {
        $event = $eventRepository->findOneBy(['slug' => $slug]);
        if (!$event instanceof Event) {
            throw new NotFoundHttpException(
                sprintf('Event "%s" not found', $slug)
            );
        }
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles() ?? [], true);
        $isOwner = $event->getOrganizer()->getId() === $user->getId();

        if (!$isAdmin && !$isOwner) {
            throw new AccessDeniedException(
                'You are not authorized to edit this event'
            );
        }

        $oldTitle = $event->getTitle();
        $form = $formFactory->create(EventType::class, $event);
        $form->handleRequest($request);

        try{
            if ($form->isSubmitted() && $form->isValid()) {
                if ($oldTitle !== $event->getTitle()) {
                    $event->setSlug(strtolower($slugger->slug($event->getTitle())->toString()));
                }
                $eventRepository->saveandpersist($event);
                return new RedirectResponse($urlGenerator->generate('event_show', [
                    'slug' => $event->getSlug(),
                ]));
            }
        } catch (\Exception $e){
            throw new \Exception('Error saving event: ' . $e->getMessage());
        }

        return new Response($twig->render('Event/form.html.twig', [
            'form' => $form->createView(),
        ]), Response::HTTP_OK);
    }
}
