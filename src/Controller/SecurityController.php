<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path:"/login", name:"app_login")]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if($this->getUser() !== null){
            switch ($this->getUser()->getRoles()[0]){
                case "CUSTOMER": return $this->redirectToRoute('app_homepage');
                case 'ADMIN': return $this->redirectToRoute('admin_dashboard');
            }
        } 

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
       
    }
}
