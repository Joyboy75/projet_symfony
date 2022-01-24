<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminImageController extends AbstractController
{
    /**
     * @Route("/admin/image", name="admin_image")
     */
    public function index(): Response
    {
        return $this->render('admin_image/index.html.twig', [
            'controller_name' => 'AdminImageController',
        ]);
    }

    /**
     * @Route("admin/images", name="admin_image_list")
     */
    public function imageList(ImageRepository $imageRepository){

        $images = $imageRepository->findAll();

        return $this->render('admin/images.html.twig', ['images' => $images]);
    }

    public function image_show($id, ImageRepository $imageRepository)
    {
        $image = $imageRepository->find($id);

        return $this->render("admin/image.html.twig", ['image' => $image]);
    }

    /**
     * @Route("/admin/create/image", name="admin_create_image")
     */
    public function adminCreateImage(
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        SluggerInterface $sluggerInterface
    ) {
        $image = new Image();

        $imageForm = $this->createForm(ImageType::class, $image);

        $imageForm->handleRequest($request);

        if ($imageForm->isSubmitted() && $imageForm->isValid()) {

            // On récupère le fichier
            $mediaFile = $imageForm->get('src')->getData();

            if ($mediaFile) {

                // On créée un nom unique à notre fichier à partir du nom original
                // Pour éviter tout problème de confusion

                // On récupère le nom original du fichier
                $originalFilename = pathinfo($mediaFile->getClientOriginalName(), PATHINFO_FILENAME);

                // On utilise slug sur le nom original pour avoir un nom valide du fichier
                $safeFilename = $sluggerInterface->slug($originalFilename);

                // On ajoute un id unique au nom de l'image
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $mediaFile->guessExtension();

                // On déplace le fichier dans le dossier public/image
                // la destination du fichier est enregistré dans 'images_directory'
                // qui est défini dans le fichier config\services.yaml

                $mediaFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );

                $image->setSrc($newFilename);
            }

            $entityManagerInterface->persist($image);

            $entityManagerInterface->flush();

            return $this->redirectToRoute("admin_product_list");
        }

        return $this->render("admin/imageform.html.twig", ['imageForm' => $imageForm->createView()]);
    }

    /**
     * @Route("/admin/update/image/{id}", name="admin_update_image")
     */
    public function adminUpdateImage($id,
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        SluggerInterface $sluggerInterface,
        ImageRepository $imageRepository
    ) {
        $image = $imageRepository->find($id);

        $imageForm = $this->createForm(ImageType::class, $image);

        $imageForm->handleRequest($request);

        if ($imageForm->isSubmitted() && $imageForm->isValid()) {

            // On récupère le fichier
            $mediaFile = $imageForm->get('src')->getData();

            if ($mediaFile) {

                // On créée un nom unique à notre fichier à partir du nom original
                // Pour éviter tout problème de confusion

                // On récupère le nom original du fichier
                $originalFilename = pathinfo($mediaFile->getClientOriginalName(), PATHINFO_FILENAME);

                // On utilise slug sur le nom original pour avoir un nom valide du fichier
                $safeFilename = $sluggerInterface->slug($originalFilename);

                // On ajoute un id unique au nom de l'image
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $mediaFile->guessExtension();

                // On déplace le fichier dans le dossier public/image
                // la destination du fichier est enregistré dans 'images_directory'
                // qui est défini dans le fichier config\services.yaml

                $mediaFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );

                $image->setSrc($newFilename);
            }

            $entityManagerInterface->persist($image);

            $entityManagerInterface->flush();

            return $this->redirectToRoute("admin_product_list");
        }

        return $this->render("admin/imageform.html.twig", ['imageForm' => $imageForm->createView()]);
    }

    /**
     * @Route("/admin/delete/image/{id}", name="admin_delete_image")
     */
    public function adminDeleteImage(
        $id,
        ImageRepository $imageRepository,
        EntityManagerInterface $entityManagerInterface
        ){

            $image = $imageRepository->find($id);

            //remove supprime le image et flush enregistre ds la bdd
            $entityManagerInterface->remove($image);
            $entityManagerInterface->flush();

            $this->addFlash(
                'notice',
                'Votre image a bien été supprimée'
            );

            return $this->redirectToRoute('admin_product_list');

    }
    
}

