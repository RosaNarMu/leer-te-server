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
use App\Repository\CommentRepository;
use App\Repository\StoryRepository;


/**
 * @Route("admin")
 */

class AdminController extends AbstractController
{

    private $em;
    private $userRepository;
    private $storyRepository;
    private $commentRepository;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository, StoryRepository $storyRepository, CommentRepository $commentRepository)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->storyRepository = $storyRepository;
        $this->commentRepository = $commentRepository;
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
                'code' => 200,
                'content' => $content
            ]
        );
    }

    /**
     * @Route("/dataStory", name="data_story_admin", methods={"GET"})
     */
    public function adminStory(StoryRepository $storyRepository): Response
    {
        $story = $this->storyRepository->findBy(['published' => 1, 'isActive' => true], []);

        $result = [];

        foreach ($story as $story) {
            $result[] = [
                'id' => $story->getId(),
                'title' => $story->getTitle(),
                'content' => $story->getContent(),
                'publicationDate' => $story->getPublicationDate(),
                'User' => $story->getUser()->getNickName(),
                'genre' => $story->getGenre(),
                'published' => $story->getPublished()
            ];
        }

        return new JsonResponse($result);
    }


    /**
     * @Route("/dataUser", name="data_user_admin", methods={"GET"})
     */
    public function adminUser(): Response
    {
        $user = $this->userRepository->findBy([], [/* 'nickName' => 'ASC' */]);

        $result = [];

        foreach ($user as $user) {
            $result[] = [
                'id' => $user->getId(),
                'nickName' => $user->getNickName(),
                'email' => $user->getEmail(),
            ];
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/dataComment", name="data_comment_admin", methods={"GET"})
     */
    public function adminComment(): Response
    {
        $comment = $this->commentRepository->findBy([], []);

        $result = [];

        foreach ($comment as $comment) {
            $result[] = [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'publicationDate' => $comment->getPublicationDate(),
                'score' => $comment->getScore(),
                'User' => $comment->getUser()->getNickName(),
                'Story' => $comment->getStory()->getTitle(),
            ];
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/deleteStory/{id}", name="delete_story_admin", methods={"DELETE"})
     */

    public function deleteStory($id): Response
    {
        $story = $this->storyRepository->find($id);

        $story->setIsActive(false);

        $this->em->flush();
        return new JsonResponse([
            'code' => 200
        ]);
    }

    /**
     * @Route("/deleteUser/{id}", name="delete_user_admin", methods={"DELETE"})
     */

    public function deleteUser($id): Response
    {
        $user = $this->userRepository->find($id);
        $this->em->remove($user);
        $this->em->flush();
        return new JsonResponse([
            'code' => 200
        ]);
    }

    /**
     * @Route("/deleteComment/{id}", name="delete_comment_admin", methods={"DELETE"})
     */

    public function deleteComment($id): Response
    {
        $comment = $this->commentRepository->find($id);
        $this->em->remove($comment);
        $this->em->flush();
        return new JsonResponse([
            'code' => 200
        ]);
    }
}
