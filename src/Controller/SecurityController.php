<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout():Response
    {
        return $this->redirect($this->generateUrl('app_home'));
    }

    #[Route('/createUser', name: 'create_user')]
    public function createUser(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {

        $user = new Utilisateur();
        $hashedPassword = $userPasswordHasher->hashPassword($user, '123a*b');

        $user->setPassword($hashedPassword);
        $user->setEmail('a2@gmail.com');
        $user->setRoles(['ROLE_ADMIN']);
        $entityManager->persist($user); 
        $entityManager->flush();
        

        return $this->render('login/index.html.twig', [
            
            
        ]);
    }
}
