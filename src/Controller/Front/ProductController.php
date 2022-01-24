<?php

namespace App\Controller\Front;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    ProductRepository $productRepository){

        $product = $productRepository->find($id);

        return $this->render("front/product.html.twig", ['product' => $product]);

    }
}
