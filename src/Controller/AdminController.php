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
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Symfony\Component\String\Slugger\AsciiSlugger;


/**
 * @Route("admin")
 */

class AdminController extends AbstractController
{

    private $em;
    private $userRepository;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/add", name="add_user_admin", methods={"POST"})
     */

    public function addUserAdmin(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $content = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($content['email']);
        $user->setNickName($content['nickName']);
        $user->setRoles($content['roles']);


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
