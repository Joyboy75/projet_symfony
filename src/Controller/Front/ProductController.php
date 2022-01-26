<?php

namespace App\Controller\Front;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\UserRepository;
use App\Repository\CommentRepository;
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
}
