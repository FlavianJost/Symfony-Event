<?php

namespace App\Controller\User;

use App\Entity\User;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment;

#[Route('/profile', name: 'app_profile')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class UserProfileController
{
    public function __invoke(Request $request,Environment $twig, FormFactoryInterface $formFactory, #[CurrentUser] User $user) : Response
    {
        return new Response($twig->render('user/profile.html.twig', [
            'user' => $user
        ]));
    }
}
