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
use App\Entity\Favorites;
use App\Entity\Story;
use App\Repository\CategoriaRepository;
use App\Repository\EntradaRepository;
use App\Repository\FavoritesRepository;
use App\Repository\StoryRepository;
use DateTimeInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @Route("favorites")
 */

class FavoritesController extends AbstractController
{

    private $em;
    private $favoritesRepository;

    public function __construct(EntityManagerInterface $em, FavoritesRepository $favoritesRepository)
    {
        $this->em = $em;
        $this->favoritesRepository = $favoritesRepository;
    }

    /**
     * @Route("/data", name="data_favorites", methods={"GET"})
     */
    public function readFavorites(): Response
    {
        $favorites = $this->favoritesRepository->findBy([], ['Story' => 'ASC']);

        $result = [];

        foreach ($favorites as $favorites) {
            $result[] = [
                'User' => $favorites->getUser()->getNickName(),
                'Story ' => $favorites->getStory()->getTitle(),
            ];
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/add", name="add_favorite", methods={"POST"})
     */

    public function addFavorites(Request $request, UserRepository $userRepository, StoryRepository $storyRepository): Response
    {
        $content = json_decode($request->getContent(), true);

        $user = $userRepository->findOneBy(['nickName' => $content['user']]);

        $story = $storyRepository->findOneBy(['title' => $content['story']]);

        $favorites = new Favorites();

        $favorites->setUser($user);

        $favorites->setStory($story);

        $this->em->persist($favorites);

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
     * @Route("/delete/{id}", name="delete_favorite", methods={"DELETE"})
     */
    public function delete($id): Response
    {
        $favorites = $this->favoritesRepository->find($id);
        $this->em->remove($favorites);
        $this->em->flush();
        return new JsonResponse(['respuesta' => 'ok']);
    }
}
