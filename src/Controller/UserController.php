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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
     * @Route("", methods={"OPTIONS"})
     */
    public function options(): Response
    {
        return new Response('');
    }

    /**
     * @Route("/dataFromUser", name="data_user_from_user", methods={"GET"})
     */

    public function UserFromUser(): Response
    {
        /** @var User $user */
        $userLogin = $this->getUser();

        $user = $this->userRepository->findBy(['id' => $userLogin], []);

        $result = [];

        foreach ($user as $user) {
            $result[] = [
                'id' => $user->getId(),
                'userLogin' => $userLogin->getNickName()
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


    /**
     * @Route("/edit", name="edit_user", methods={"PUT"})
     */
    public function update(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {

        /** @var User $user */
        $userLogin = $this->getUser();

        $id = $userLogin->getId();

        $content = json_decode($request->getContent(), true);
        $user = $this->userRepository->find($id);

        if (isset($content['nickName']) && $content['nickName'] != "") {
            $user->setNickName($content['nickName']);
        }

        if (isset($content['email']) && $content['email'] != "") {
            $user->setEmail($content['email']);
        }

        if (isset($content['password']) && $content['password'] != "") {
            $simplePassword = ($content['password']);
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $simplePassword
            );
            $user->setPassword($hashedPassword);
        }

        $this->em->flush();

        return new JsonResponse(['respuesta' => 'ok']);
    }


    /**
     * @Route("/delete", name="delete_user", methods={"DELETE"})
     */

    public function delete(): Response
    {
        /** @var User $user */
        $userLogin = $this->getUser();

        $id = $userLogin->getId();

        $user = $this->userRepository->find($id);
        $this->em->remove($user);
        $this->em->flush();
        return new JsonResponse(['respuesta' => 'ok']);
    }
}
