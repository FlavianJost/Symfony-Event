<?php

namespace App\Controller\Security;

use Symfony\Component\Routing\Attribute\Route;

#[Route('/logout', name: 'app_logout')]
class LogoutController
{
    public function __invoke()
    {
        
    }
}
