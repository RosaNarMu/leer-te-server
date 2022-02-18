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
use App\Entity\Story;
use App\Repository\CategoriaRepository;
use App\Repository\EntradaRepository;
use App\Repository\StoryRepository;
use DateTimeInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @Route("story")
 */

class StoryController extends AbstractController
{
    private $em;
    private $storyRepository;

    public function __construct(EntityManagerInterface $em, StoryRepository $storyRepository)
    {
        $this->em = $em;
        $this->storyRepository = $storyRepository;
    }

    /**
     * @Route("/data", name="data_story", methods={"GET"})
     */
    public function readStory(): Response
    {
        $story = $this->storyRepository->findBy([], ['publicationDate' => 'ASC']);

        $result = [];

        foreach ($story as $story) {
            $result[] = [
                'id' => $story->getId(),
                'title' => $story->getTitle(),
                'content' => $story->getContent(),
                'publicationDate' => $story->getPublicationDate(),
                'User' => $story->getUser(),
                'genre' => $story->getGenre(),
                'published' => $story->getPublished()
            ];
        }

        return new JsonResponse($result);
    }


    /**
     * @Route("/add", name="add_story", methods={"POST"})
     */

    public function addStory(Request $request, UserRepository $userRepository): Response
    {
        $content = json_decode($request->getContent(), true);

        $user = $userRepository->findOneBy(['nickName' => $content['user']]);

        $story = new Story();

        $story->setTitle($content['title']);

        $story->setContent($content['content']);

        $date = new \DateTime('@' . strtotime('now'));
        $story->setPublicationDate($date);

        $story->setUser($user);

        $story->setGenre($content['genre']);

        $story->setPublished($content['published']);

        $this->em->persist($story);

        $this->em->flush();

        return new JsonResponse(
            [
                'result' => 'ok',
                'code' => 200,
                'content' => $content

            ]
        );
    }

    /**
     * @Route("/edit/{id}", name="edit_story", methods={"PUT"})
     */
    public function update(Request $request, $id, UserRepository $userRepository): Response
    {
        $content = json_decode($request->getContent(), true);
        $story = $this->storyRepository->find($id);

        if (isset($content['title'])) {
            $story->setTitle($content['title']);
        }

        if (isset($content['content'])) {
            $story->setContent($content['content']);
        }

        if (isset($content['date'])) {
            $date = new \DateTime('@' . strtotime('now'));
            $story->setPublicationDate($date);
        }

        if (isset($content['user'])) {
            $user = $userRepository->findOneBy(['nickName' => $content['user']]);
            $story->setUser($user);
        }

        if (isset($content['genre'])) {
            $story->setGenre($content['genre']);
        }

        if (isset($content['published'])) {
            $story->setPublished($content['published']);
        }

        $this->em->flush();

        return new JsonResponse(['respuesta' => 'ok']);
    }

    /**
     * @Route("/delete/{id}", name="delete_story", methods={"DELETE"})
     */

    public function delete($id): Response
    {
        $story = $this->storyRepository->find($id);
        $this->em->remove($story);
        $this->em->flush();
        return new JsonResponse(['respuesta' => 'ok']);
    }
}
