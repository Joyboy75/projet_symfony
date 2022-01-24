<?php

namespace App\Controller\Front;

use App\Repository\LicenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LicenceController extends AbstractController
{
    /**
     * @Route("/licence", name="licence")
     */
    public function index(): Response
    {
        return $this->render('licence/index.html.twig', [
            'controller_name' => 'LicenceController',
        ]);
    }

     /**
     * @Route("licences", name="licence_list")
     */
    public function licenceList(
        LicenceRepository $licenceRepository
    ){

        $licences = $licenceRepository->findAll();

        return $this->render("front/licences.html.twig", ["licences"=>$licences]);
        
    }

    /**
     * @Route("licence/{id}", name="licence_show")
     */
    public function licenceShow($id,
    LicenceRepository $licenceRepository){

        $licence = $licenceRepository->find($id);

        return $this->render("front/licence.html.twig", ['licence' => $licence]);

    }
}
