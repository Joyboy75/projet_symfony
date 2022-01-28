<?php

namespace App\Controller\Front;

use App\Entity\Like;
use App\Entity\Comment;
use App\Entity\Dislike;
use App\Form\CommentType;
use App\Repository\LikeRepository;
use App\Repository\UserRepository;
use App\Repository\CommentRepository;
use App\Repository\DislikeRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    /**
     * @Route("/product", name="product")
     */
    public function index(): Response
    {
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }
    
    /**
     * @Route("products", name="product_list")
     */
    public function productList(
        ProductRepository $productRepository
    ){

        $products = $productRepository->findAll();

        return $this->render("front/products.html.twig", ["products"=>$products]);
        
    }

    /**
     * @Route("product/{id}", name="product_show")
     */
    public function productShow($id,
    ProductRepository $productRepository,
    UserRepository $userRepository,
    EntityManagerInterface $entityManagerInterface,
    Request $request
    ){

        $product = $productRepository->find($id);

        $comment = new Comment();

        $commentForm = $this->createForm(CommentType::class, $comment);

        $commentForm->handleRequest($request);

        if($commentForm->isSubmitted() && $commentForm->isValid()){

            $user = $this->getUser();
            if ($user) {
                $user_mail = $user->getUserIdentifier();
                $user = $userRepository->findOneBy(['email' => $user_mail]);

                $comment->setUser($user);
                $comment->setProduct($product);
                $comment->setDate(new \DateTime("NOW"));

                $entityManagerInterface->persist($comment);
                $entityManagerInterface->flush();
            }
        }

        return $this->render("front/product.html.twig", [
            'product' => $product,
            'commentForm' => $commentForm->createView()
        ]);



    }

    /**
     * @Route("comment/product/{id}", name="product_comment")
     */
    public function commentProduct(
        $id,
        ProductRepository $productRepository,
        CommentRepository $commentRepository,
        EntityManagerInterface $entityManagerInterface
    ) {

        $product = $productRepository->find($id);
        $user = $this->getUser();

        if (!$user) {
            return $this->json(
                [
                    'code' => 403,
                    'message' => "Vous devez vous connecter pour commenter ce product"
                ],
                403
            );
        }

        $comment = new Comment();

        $comment->setProduct($product);
        $comment->setUser($user);

        $entityManagerInterface->persist($comment);
        $entityManagerInterface->flush();

        return $this->json([
            'code' => 200,
            'message' => "Comment ajouté !",
            'comments' => $commentRepository->count(['product' => $product])
        ], 200);
    }


    /**
     * @Route("search", name="front_search")
     */
    public function frontSearch(Request $request, ProductRepository $productRepository)
    {

        // Récupérer les données rentrées dans le formulaire
        $term = $request->query->get('term');
        // query correspond à l'outil qui permet de récupérer les données d'un formulaire en get
        // pour un formulaire en post on utilise request

        $products = $productRepository->searchByTerm($term);

        return $this->render('front/search.html.twig', ['products' => $products, 'term' => $term]);
    }

     /**
     * @Route("like/product/{id}", name="product_like")
     */
    public function likeProduct(
        $id,
        ProductRepository $productRepository,
        LikeRepository $likeRepository,
        EntityManagerInterface $entityManagerInterface,
        DislikeRepository $dislikeRepository
    ) {

        $product = $productRepository->find($id);
        $user = $this->getUser();

        if (!$user) {
            return $this->json(
                [
                    'code' => 403,
                    'message' => "Vous devez vous connecter"
                ],
                403
            );
        }

        if ($product->isLikeByUser($user)) {
            $like = $likeRepository->findOneBy(
                [
                    'product' => $product,
                    'user' => $user
                ]
            );

            $entityManagerInterface->remove($like);
            $entityManagerInterface->flush();

            return $this->json([
                'code' => 200,
                'message' => "Like supprimé",
                'likes' => $likeRepository->count(['product' => $product])
            ], 200);
        }

        if ($product->isDislikeByUser($user)) {
            $dislike = $dislikeRepository->findOneBy([
                'product' => $product,
                'user' => $user
            ]);

            $entityManagerInterface->remove($dislike);

            $like = new Like();

            $like->setProduct($product);
            $like->setUser($user);

            $entityManagerInterface->persist($like);
            $entityManagerInterface->flush();

            return $this->json([
                'code' => 200,
                'message' => "Like ajouté et dislike supprimé",
                'likes' => $likeRepository->count(['product' => $product]),
                'dislikes' => $dislikeRepository->count(['product' => $product])
            ], 200);
        }


        $like = new Like();

        $like->setProduct($product);
        $like->setUser($user);

        $entityManagerInterface->persist($like);
        $entityManagerInterface->flush();

        return $this->json([
            'code' => 200,
            'message' => "Like ajouté",
            'likes' => $likeRepository->count(['product' => $product])
        ], 200);
    }

    /**
     * @Route("dislike/product/{id}", name="product_dislike")
     */
    public function dislikeProduct(
        $id,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManagerInterface,
        DislikeRepository $dislikeRepository,
        LikeRepository $likeRepository
    ) {

        $product = $productRepository->find($id);
        $user = $this->getUser();

        if (!$user) {
            return $this->json(
                [
                    'code' => 403,
                    'message' => "Vous devez vous connecter"
                ],
                403
            );
        }

        if ($product->isDislikeByUser($user)) {
            $dislike = $dislikeRepository->findOneBy([
                'product' => $product,
                'user' => $user
            ]);

            $entityManagerInterface->remove($dislike);
            $entityManagerInterface->flush();

            return $this->json([
                'code' => 200,
                'message' => "Le dislike a été supprimé",
                'dislikes' => $dislikeRepository->count(['product' => $product])
            ], 200);
        }

        if ($product->isLikeByUser($user)) {
            $like = $likeRepository->findOneBy([
                'product' => $product,
                'user' => $user
            ]);

            $entityManagerInterface->remove($like);

            $dislike = new Dislike();
            $dislike->setproduct($product);
            $dislike->setUser($user);

            $entityManagerInterface->persist($dislike);
            $entityManagerInterface->flush();

            return $this->json([
                'code' => 200,
                'message' => "like supprimé et dislike ajouté",
                'dislikes' => $dislikeRepository->count(['product' => $product]),
                'likes' => $likeRepository->count(['product' => $product])
            ], 200);
        }


        $dislike = new Dislike();

        $dislike->setProduct($product);
        $dislike->setUser($user);

        $entityManagerInterface->persist($dislike);
        $entityManagerInterface->flush();

        return $this->json([
            'code' => 200,
            'message' => "Dislike ajouté",
            'dislikes' => $dislikeRepository->count(['product' => $product])
        ], 200);
    }
}
