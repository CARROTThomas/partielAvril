<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', methods: ["POST"])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $manager,
        UserRepository $userRepository,
        SerializerInterface $serializer
    ): Response
    {
        $json = $request->getContent();
        $user = $serializer->deserialize($json, User::class, 'json');

        // encode the plain password
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $user->getPassword()
            )
        );

        $taken = $userRepository->findOneBy(['username'=> $user->getUsername()]);
        if (!$taken){

           // associer profile a user
            $user->setProfile(new Profile());
            $profile = $user->getProfile();
            $profile->setUsername($user->getUsername());

            $manager->persist($user);
            $manager->flush();

            return $this->json("registered. welcome", 200);
        } else {
            return $this->json("username taken", 401);
        }

    }
}
