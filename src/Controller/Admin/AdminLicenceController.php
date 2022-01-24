<?php

namespace App\Controller\Admin;

use App\Entity\Licence;
use App\Form\LicenceType;
use App\Repository\LicenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminLicenceController extends AbstractController{
    //Récupération de tous les éléments de la table licence
    /**
     * @Route("admin/licences", name="admin_licence_list")
     */
    public function adminLicenceList(LicenceRepository $licenceRepository){

        $licences = $licenceRepository->findAll();

        return $this->render("admin/licences.html.twig", ['licences' => $licences ]);
    }

    //Récupération d'un élément de la table licence
    /**
     * @Route("admin/licence/{id}", name="admin_licence_show")
     */
    public function adminLicenceShow($id,
    LicenceRepository $licenceRepository
    ){
        $licence = $licenceRepository->find($id);

        return $this->render("admin/licence.html.twig", ['licence'=> $licence]);
    }
     /**
     * @Route("admin/create/licence", name="admin_create_licence")
     */
    public function adminCreateLicence(
        Request $request,
        EntityManagerInterface $entityManagerInterface
    ) {

        $licence = new Licence();

        $licenceForm = $this->createForm(LicenceType::class, $licence);

        $licenceForm->handleRequest($request);

        if ($licenceForm->isSubmitted() && $licenceForm->isValid()) {
            $entityManagerInterface->persist($licence);
            $entityManagerInterface->flush();
            
            return $this->redirectToRoute("admin_licence_list");
        }

        return $this->render('admin/licenceform.html.twig', ['licenceForm' => $licenceForm->createView()]);
    }

    //Modification d'un élément de la table licence grâce à son id
    /**
     *@Route("admin/update/licence/{id}", name="admin_update_licence")
     */
    public function adminUpdateLicence(
        $id,
        LicenceRepository $licenceRepository,
        Request $request, // class permettant d'utiliser le formulaire de récupérer les information 
        EntityManagerInterface $entityManagerInterface // class permettantd'enregistrer ds la bdd
    ) {
        $licence = $licenceRepository->find($id);

        // Création du formulaire
        $licenceForm = $this->createForm(LicenceType::class, $licence);

        // Utilisation de handleRequest pour demander au formulaire de traiter les informations
        // rentrées dans le formulaire
        // Utilisation de request pour récupérer les informations rentrées dans le formualire
        $licenceForm->handleRequest($request);


        if ($licenceForm->isSubmitted() && $licenceForm->isValid()) {
            // persist prépare l'enregistrement ds la bdd analyse le changement à faire
            $entityManagerInterface->persist($licence);
            $id = $licenceRepository->find($id);

            // flush enregistre dans la bdd
            $entityManagerInterface->flush();

            $this->addFlash(
                'notice',
                'Le licence a bien été modifié !'
            );

            return $this->redirectToRoute('admin_licence_list');
        }

        return $this->render('admin/licenceform.html.twig', ['licenceForm' => $licenceForm->createView()]);
    }

        //Suppression d'un élément de la table licence grâce à son id

    /**
     * @Route("admin/delete/licence/{id}", name="admin_delete_licence")
     */
    public function adminDeleteLicence(
        $id,
        LicenceRepository $licenceRepository,
        EntityManagerInterface $entityManagerInterface
    ) {

        $licence = $licenceRepository->find($id);

        //remove supprime le licence et flush enregistre ds la bdd
        $entityManagerInterface->remove($licence);
        $entityManagerInterface->flush();

        $this->addFlash(
            'notice',
            'Votre licence a bien été supprimé'
        );

        return $this->redirectToRoute('admin_licence_list');
    }
}