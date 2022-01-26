<?php

namespace App\Controller\Front;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController{

    /**
     * @Route("/home", name="front_home")
     */
    public function home(CategoryRepository $categoryRepository){

        $id = rand(1,5);
        $category = $categoryRepository->find($id);

        if($category){

            return $this->render('front/home.html.twig', ['category' => $category]);
        }else{
            return $this->redirectToRoute('front_home');

        }
    }
}