<?php

namespace App\Controller;

use DateTime;
use App\Entity\Vehicule;
use App\Form\VehiculeType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VehiculeController extends AbstractController
{
    public function allVehicules(ManagerRegistry $doctrine)
    {
        $vehicules = $doctrine->getRepository(Vehicule::class)->findAll();
        return $this->render('vehicule/allVehicules.html.twig',[
            'vehicules' => $vehicules
        ]);
    }

    public function add(ManagerRegistry $doctrine, Request $request)
    {
        $vehicule = new Vehicule;

        $form = $this->createform(VehiculeType::class, $vehicule);
        $form->handlerequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $vehicule->setDateEnregistrement(new DateTime("now"));
            $manager = $doctrine->getManager();
            $manager->persist($vehicule);
            $manager->flush();

            return $this->redirectToRoute("app_allVehicules");
        }
        return $this->render('vehicule/formulaire.html.twig',[
            'formVehicule'=> $form->createView()]);
    }

    public function update(ManagerRegistry $doctrine, Request $request, $id)
    {
        $vehicule = $doctrine->getRepository(Vehicule::class)->find($id);
        $form = $this->createform(VehiculeType::class, $vehicule);
        $form->handlerequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $manager = $doctrine-getManager();
            $manager->persist($vehicule);
            $manager->flush();

            return $this-redictToRoute('app_allVehicules');
        }

        return $this->render('vehicule/formulaire.html.twig', [
            'formVehicule' => $form->createView()
        ]);
    }

    public function delete(ManagerRegistry $doctrine, $id)
    {
        $vehicule = $doctrine->getRepository(Vehicule::class)->find($id);
        $manager = $doctrine->getManager();
        $manager->remove($vehicule);
        $manager->flush();

        return $this->redirectToRoute("app_allVehicules");
    }

    public function select(ManagerRegistry $doctrine, $id)
    {
        $vehicule = $doctrine->getRepository(Vehicule::class)->find($id);
        return $this->render('vehicule/vehicule.html.twig',[
            'vehicule'=> $vehicule
        ]);
    }
}
