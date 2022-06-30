<?php

namespace App\Controller;

use DateTime;
use App\Entity\Vehicule;
use App\Form\VehiculeType;
use App\Repository\VehiculeRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class VehiculeController extends AbstractController
{
    public function allVehicules(ManagerRegistry $doctrine)
    {
        $vehicules = $doctrine->getRepository(Vehicule::class)->findAll();
        return $this->render('vehicule/admin/adminVehicules.html.twig',[
            'vehicules' => $vehicules
        ]);
    }

    public function add(ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger)
    {
        $vehicule = new Vehicule;

        $form = $this->createform(VehiculeType::class, $vehicule);
        $form->handlerequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            // On récupère l'image depuis le formulaire
            $file = $form->get('imageForm')->getData();
            //dd($file);
            //dd($vehicule);
            // le slug permet de modifier une chaine de caractères et enlève les caractères spéciaux
            $fileName = $slugger->slug($vehicule->getTitre()) . uniqid() . '.' . $file->guessExtension();

            try{
                //On déplace le fichier image récupéré depuis le formulaire dans le dossier parametré dans la partie Parameters du fichier config/service.yaml, avec pour nom $fileName
                $file->move($this->getParameter('photos_vehicules'), $fileName);
            }catch(FileException $e){
                //gérer les exceptions en cas d'erreur durant l'upload
            }
            $vehicule->setPhoto($fileName);
            $vehicule->setDateEnregistrement(new DateTime("now"));
            $manager = $doctrine->getManager();
            $manager->persist($vehicule);
            $manager->flush();

            return $this->redirectToRoute("admin_app_allVehicules");
        }
        return $this->render('vehicule/admin/formulaire.html.twig',[
            'formVehicule'=> $form->createView()]);
    }

    public function update(ManagerRegistry $doctrine, Request $request, $id, SluggerInterface $slugger)
    {
        $vehicule = $doctrine->getRepository(Vehicule::class)->find($id);
        $form = $this->createform(VehiculeType::class, $vehicule);
        $form->handlerequest($request);
        // on stock l'image du vehicule à mettre à jour
        $image = $vehicule->getPhoto();
        if($form->isSubmitted() && $form->isValid())
        {
            // si une image a bien été ajouté au formulaire
            if($form->get('imageForm')->getData() )
            {
                // on recupere l'image du formulaire
                $imageFile = $form->get('imageForm')->getData();
    
                //on crée un nouveau nom pour l'image
                $fileName = $slugger->slug($vehicule->getTitre()) . uniqid() . '.' . $imageFile->guessExtension();
    
                //on deplace l'image dans le dossier parametré dans service.yaml
                try{
                    $imageFile->move($this->getParameter('photos_vehicules'), $fileName);
                }catch(FileException $e){
                    // gestion des erreur upload
                }
                $vehicule->setPhoto($fileName);
            }
            
            $manager = $doctrine->getManager();
            $manager->persist($vehicule);
            $manager->flush();

            return $this->redirectToRoute('admin_app_allVehicules');
        }

        return $this->render('vehicule/admin/formulaire.html.twig', [
            'formVehicule' => $form->createView()
        ]);
    }

    public function delete($id, VehiculeRepository $repo)
    {
        $vehicule = $repo->find($id);
        // dd($vehicule);
        $repo->remove($vehicule, 1);

        return $this->redirectToRoute("admin_app_allVehicules");
    }

    public function select(ManagerRegistry $doctrine, $id)
    {
        $vehicule = $doctrine->getRepository(Vehicule::class)->find($id);
        return $this->render('vehicule/admin/vehicule.html.twig',[
            'vehicule'=> $vehicule
        ]);
    }
}
