<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminProductController extends AbstractController
{

    //Récupération de tous les éléments de la table Product
    /**
     * @Route("admin/products", name="admin_product_list")
     */
    public function adminProductList(ProductRepository $productRepository)
    {

        $products = $productRepository->findAll();

        return $this->render("admin/products.html.twig", ['products' => $products]);
    }

    //Récupération d'un élément de la table Product
    /**
     * @Route("admin/product/{id}", name="admin_product_show")
     */
    public function adminProductShow(
        $id,
        ProductRepository $productRepository
    ) {
        $product = $productRepository->find($id);

        return $this->render("admin/product.html.twig", ['product' => $product]);
    }

    /**
     * @Route("admin/create/product", name="admin_create_product")
     */
    public function adminCreateProduct(
        Request $request,
        EntityManagerInterface $entityManagerInterface
    ) {

        $product = new Product();

        $productForm = $this->createForm(ProductType::class, $product);

        $productForm->handleRequest($request);

        if ($productForm->isSubmitted() && $productForm->isValid()) {
            $entityManagerInterface->persist($product);
            $entityManagerInterface->flush();
            
            return $this->redirectToRoute("admin_product_list");
        }

        return $this->render('admin/productform.html.twig', ['productForm' => $productForm->createView()]);
    }

    //Modification d'un élément de la table product grâce à son id
    /**
     *@Route("admin/update/product/{id}", name="admin_update_product")
     */
    public function adminUpdateProduct(
        $id,
        ProductRepository $productRepository,
        Request $request, // class permettant d'utiliser le formulaire de récupérer les information 
        EntityManagerInterface $entityManagerInterface // class permettantd'enregistrer ds la bdd
    ) {
        $product = $productRepository->find($id);

        // Création du formulaire
        $productForm = $this->createForm(ProductType::class, $product);

        // Utilisation de handleRequest pour demander au formulaire de traiter les informations
        // rentrées dans le formulaire
        // Utilisation de request pour récupérer les informations rentrées dans le formualire
        $productForm->handleRequest($request);


        if ($productForm->isSubmitted() && $productForm->isValid()) {
            // persist prépare l'enregistrement ds la bdd analyse le changement à faire
            $entityManagerInterface->persist($product);
            $id = $productRepository->find($id);

            // flush enregistre dans la bdd
            $entityManagerInterface->flush();

            $this->addFlash(
                'notice',
                'Le product a bien été modifié !'
            );

            return $this->redirectToRoute('admin_product_list');
        }

        return $this->render('admin/productform.html.twig', ['productForm' => $productForm->createView()]);
    }

        //Suppression d'un élément de la table product grâce à son id

    /**
     * @Route("admin/delete/product/{id}", name="admin_delete_product")
     */
    public function adminDeleteProduct(
        $id,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManagerInterface
    ) {

        $product = $productRepository->find($id);

        //remove supprime le product et flush enregistre ds la bdd
        $entityManagerInterface->remove($product);
        $entityManagerInterface->flush();

        $this->addFlash(
            'notice',
            'Votre product a bien été supprimé'
        );

        return $this->redirectToRoute('admin_product_list');
    }
}

