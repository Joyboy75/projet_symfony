<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminCategoryController extends AbstractController
{
    //Récupération de tous les éléments de la table category
    /**
     * @Route("admin/categories", name="admin_category_list")
     */
    public function adminCategoryList(CategoryRepository $categoryRepository){

        $categories = $categoryRepository->findAll();

        return $this->render("admin/categories.html.twig", ['categories' => $categories ]);
    }

    //Récupération d'un élément de la table category
    /**
     * @Route("admin/category/{id}", name="admin_category_show")
     */
    public function adminCategoryShow($id,
    CategoryRepository $categoryRepository
    ){
        $category = $categoryRepository->find($id);

        return $this->render("admin/category.html.twig", ['category'=> $category]);
    }
    
     /**
     * @Route("admin/create/category", name="admin_create_category")
     */
    public function adminCreateCategory(
        Request $request,
        EntityManagerInterface $entityManagerInterface
    ) {

        $category = new Category();

        $categoryForm = $this->createForm(CategoryType::class, $category);

        $categoryForm->handleRequest($request);

        if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {
            $entityManagerInterface->persist($category);
            $entityManagerInterface->flush();
            
            return $this->redirectToRoute("admin_category_list");
        }

        return $this->render('admin/categoryform.html.twig', ['categoryForm' => $categoryForm->createView()]);
    }

    //Modification d'un élément de la table category grâce à son id
    /**
     *@Route("admin/update/category/{id}", name="admin_update_category")
     */
    public function adminUpdateCategory(
        $id,
        CategoryRepository $categoryRepository,
        Request $request, // class permettant d'utiliser le formulaire de récupérer les information 
        EntityManagerInterface $entityManagerInterface // class permettantd'enregistrer ds la bdd
    ) {
        $category = $categoryRepository->find($id);

        // Création du formulaire
        $categoryForm = $this->createForm(CategoryType::class, $category);

        // Utilisation de handleRequest pour demander au formulaire de traiter les informations
        // rentrées dans le formulaire
        // Utilisation de request pour récupérer les informations rentrées dans le formualire
        $categoryForm->handleRequest($request);


        if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {
            // persist prépare l'enregistrement ds la bdd analyse le changement à faire
            $entityManagerInterface->persist($category);
            $id = $categoryRepository->find($id);

            // flush enregistre dans la bdd
            $entityManagerInterface->flush();

            $this->addFlash(
                'notice',
                'Le category a bien été modifié !'
            );

            return $this->redirectToRoute('admin_category_list');
        }

        return $this->render('admin/categoryform.html.twig', ['categoryForm' => $categoryForm->createView()]);
    }

        //Suppression d'un élément de la table category grâce à son id

    /**
     * @Route("admin/delete/category/{id}", name="admin_delete_category")
     */
    public function adminDeleteCategory(
        $id,
        CategoryRepository $categoryRepository,
        EntityManagerInterface $entityManagerInterface
    ) {

        $category = $categoryRepository->find($id);

        //remove supprime le category et flush enregistre ds la bdd
        $entityManagerInterface->remove($category);
        $entityManagerInterface->flush();

        $this->addFlash(
            'notice',
            'Votre category a bien été supprimé'
        );

        return $this->redirectToRoute('admin_category_list');
    }
}
