<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\BlogPost;
use App\Repository\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Env\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/blog")
 */
class BlogController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BlogPostRepository $repository
    )
    {
    }

    /**
     * @Route("/{page}", name="blog_list", defaults={"page": 1}, requirements={"page"="\d+"})
     */
    public
    function list($page, Request $request)
    {
        $limit = $request->get('limit', 10);
        $items = $this->repository->findAll();

        return $this->json(
            [
                'page' => $page,
                'limit' => $limit,
                'data' => array_map(function (BlogPost $item) {
                    return $this->generateUrl('blog_by_id', ['id' => $item->getSlug()]);
                }, $items)
            ]
        );
    }

    /**
     * @Route("/post/{id}", name="blog_by_id", requirements={"id"="\d+"}, methods={"GET"})
     * @ParamConverter("post", options={"mapping": {"id": "id"}})
     */
    public function post(BlogPost $post)
    {
        return $this->json(
            $post
        );
    }

    /**
     * @Route("/post/{slug}", name="blog_by_slug", methods={"GET"})
     */
    public function postBySlug(BlogPost $post)
    {
        return $this->json(
            $post
        );
    }

    /**
     * @Route("/add", name="blog_add", methods={"POST"})
     */
    public function add(Request $request)
    {
        /** @var Serializer $serializer */
        $serializer = $this->container->get('serializer');

        /** @var BlogPost $blogPost */
        $blogPost = $serializer->deserialize($request->getContent(), BlogPost::class, 'json');

        $this->entityManager->persist($blogPost);
        $this->entityManager->flush();

        return $this->json($blogPost);
    }

    /**
     * @Route("/post/{id}", name="blog_delete", requirements={"id"="\d+"}, methods={"DELETE"})
     */
    public function delete(BlogPost $post)
    {
        $this->entityManager->remove($post);
        $this->entityManager->flush();

        return $this->json(null, \Symfony\Component\HttpFoundation\Response::HTTP_NO_CONTENT);
    }

}