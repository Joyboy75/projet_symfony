<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    /**
     * @Route("user/insert/", name="user_insert")
     */
    public function insertUser(
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        UserPasswordHasherInterface $userPasswordHasherInterface,
        MailerInterface $mailerInterface
    ) {

        $user = new User();

        $userForm = $this->createForm(UserType::class, $user);

        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $user->setRoles(["ROLE_USER"]);
            $user->setDate(new \DateTime("NOW"));

            $user_email = $userForm->get('email')->getData();
            $user_name = $userForm->get('name')->getData();
            $user_firstname = $userForm->get('firstname')->getData();

            $plainPassword = $userForm->get('password')->getData();

            $hashedPassword = $userPasswordHasherInterface->hashPassword($user, $plainPassword);

            $user->setPassword($hashedPassword);

            $entityManagerInterface->persist($user);

            $entityManagerInterface->flush();

            $email = (new TemplatedEmail())
                    ->from('test@test.com')
                    ->to($user_email)
                    ->subject('Inscription')
                    ->htmlTemplate('front/mail.html.twig')
                    ->context([
                        'name' => $user_name,
                        'firstname' => $user_firstname
                    ]);
 


                    $mailerInterface->send($email);

            return $this->redirectToRoute('car_list');
        }

        return $this->render("front/userform.html.twig", ['userForm' => $userForm->createView()]);
    }

    /**
     * @Route("update/user", name="user_update")
     */
    public function userUpdate(
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        UserPasswordHasherInterface $userPasswordHasherInterface,
        UserRepository $userRepository
    ) {

        // récupère le user connecté
        $user_connect = $this->getUser();

         $user_mail =  $user_connect->getUserIdentifier();

         $user = $userRepository->findOneBy(['email' => $user_mail]);

        $userForm = $this->createForm(UserType::class, $user);

        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {

            $plainPassword = $userForm->get('password')->getData();

            $hashedPassword = $userPasswordHasherInterface->hashPassword($user, $plainPassword);

            $user->setPassword($hashedPassword);

            $entityManagerInterface->persist($user);

            $entityManagerInterface->flush();

            return $this->redirectToRoute('front_home');
        }

        return $this->render("front/userform.html.twig", ['userForm' => $userForm->createView()]);
    }

    /**
     * @Route("delete/user", name="user_delete")
     */
    public function deleteUser(UserRepository $userRepository, EntityManagerInterface $entityManagerInterface)
    {
        $user_connect = $this->getUser();

         $user_mail = $user_connect->getUserIdentifier();

         $user = $userRepository->findOneBy(['email' => $user_mail]);

        $entityManagerInterface->remove($user);

        $entityManagerInterface->flush();

        return $this->redirectToRoute('front_home');
    }    
}
