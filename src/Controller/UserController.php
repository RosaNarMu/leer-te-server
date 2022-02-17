<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use App\Entity\Entrada;
use App\Repository\CategoriaRepository;
use App\Repository\EntradaRepository;

use Symfony\Component\String\Slugger\AsciiSlugger;


/**
 * @Route("user")
 */

class UserController extends AbstractController
{

    private $em;
    private $userRepository;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/data", name="data_user", methods={"GET"})
     */
    public function readUser(): Response
    {
        $user = $this->userRepository->findBy([], ['nickName' => 'ASC']);

        $result = [];

        foreach ($user as $user) {
            $result[] = [
                'id' => $user->getId(),
                'nickName' => $user->getNickName(),
                'email' => $user->getEmail(),
                'GoodReads' => $user->getGoodReads(),
                'birthDate' => $user->getBirthDate(),
            ];
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/add", name="add_user", methods={"POST"})
     */

    public function addUser(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $content = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($content['email']);
        $user->setNickName($content['nickName']);


        $simplePassword = ($content['password']);
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $simplePassword
        );

        $user->setPassword($hashedPassword);

        $this->em->persist($user);

        $this->em->flush();

        return new JsonResponse(
            [
                'result' => 'ok',
                'code' => 200,
                'content' => $content,
                'hashed' => $hashedPassword

            ]
        );
    }
}
