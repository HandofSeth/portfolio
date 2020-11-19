<?php

namespace App\Controller;

use App\Entity\Technologies;
use App\Form\TechnologiesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class TechnologiesController extends AbstractController
{
    /**
     * @Route("/admin/technologies", name="admin_technologies")
     */
    public function index()
    {

        $em = $this->getDoctrine()->getManager();
        $technologiesData = $em->getRepository(Technologies::class)->findAll();

        return $this->render('technologies/index.html.twig', [
            'technologiesData' => $technologiesData,
        ]);
    }

    /**
     * @Route("/admin/technologies/new", name="admin_technologies_new")
     * @param Request $request
     * $return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Request $request)
    {
        $newTechnologies = new Technologies();
        $form = $this->createForm(TechnologiesType::class, $newTechnologies);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $pictureFileName = $form->get('image_path')->getData();
            if ($pictureFileName) {
                try {
                    $oryginalFileName = pathinfo($pictureFileName->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFileName = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9] remove; Lower()', $oryginalFileName);
                    $newFileNamePhoto = $safeFileName . '-' . uniqid() . '.' . $pictureFileName->guessExtension();
                    $pictureFileName->move('download/', $newFileNamePhoto);

                    $newTechnologies->setImagePath($newFileNamePhoto);
                    $em = $this->getDoctrine()->getManager();
                    $newTechnologies->setIsPublic(0);
                    $newTechnologies->setUploadedAt(new \DateTime());
                    $newTechnologies->setModificatedAt(new \DateTime());
                    $em->persist($newTechnologies);
                    $em->flush();
                    $this->addFlash('success', 'Dodano Technologie');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Wystąpił nieoczekiwany błąd');
                }
            }

            return $this->redirectToRoute('admin_technologies');
        }


        return $this->render('technologies/new.html.twig', [
            'technologiesForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/technologies/edit/{id}", name="admin_technologies_edit")
     * @param Request $request
     * $return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $technologies = $em->getRepository(Technologies::class)->find($id);
        $form = $this->createForm(TechnologiesType::class, $technologies);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $pictureFileName = $form->get('image_path')->getData();
            if ($pictureFileName) {
                try {
                    $oryginalFileName = pathinfo($pictureFileName->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFileName = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9] remove; Lower()', $oryginalFileName);
                    $newFileNamePhoto = $safeFileName . '-' . uniqid() . '.' . $pictureFileName->guessExtension();
                    $pictureFileName->move('download/', $newFileNamePhoto);

                    $technologies->setImagePath($newFileNamePhoto);
                    $technologies->setModificatedAt(new \DateTime());
                    $em->persist($technologies);
                    $em->flush();
                    $this->addFlash('success', 'Zmodyfikowano Technologie');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Wystąpił nieoczekiwany błąd');
                }
            }
            return $this->redirectToRoute('admin_technologies');
        }


        return $this->render('technologies/new.html.twig', [
            'technologiesForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/technologies/delete/{id}", name="admin_technologies_delete")
     */
    public function delete($id)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $technologies = $em->getRepository(Technologies::class)->find($id);
            $em->remove($technologies);
            $em->flush();
            $this->addFlash('success', 'Usunięto Technologie');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Wystąpił nieoczekiwany błąd');
        }
        return $this->redirectToRoute('admin_technologies');
    }

    /**
     * @Route("/admin/technologies/set_visibility/{id}{visibility}", name="admin_technologies_set_visibility")
     */
    public function makeVisible($id, $visibility)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $technologies = $em->getRepository(Technologies::class)->find($id);
            $technologies->setModificatedAt(new \DateTime());
            $technologies->setIsPublic($visibility);
            $em->persist($technologies);
            $em->flush();
            $this->addFlash('success', 'Ustawiono aktywność');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Wystąpił nieoczekiwany błąd');
        }
        return $this->redirectToRoute('admin_technologies');
    }
}