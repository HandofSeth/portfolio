<?php

namespace App\Controller;

use App\Entity\About;
use App\Form\AboutType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\ImagesUploadService;

class AboutController extends AbstractController
{
    /**
     * @Route("/admin/about", name="admin_about")
     * @param Request $request
     * $return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, ImagesUploadService $imageUploadService)
    {
        $em = $this->getDoctrine()->getManager();
        $aboutData = $em->getRepository(About::class)->find(1);
        if ($aboutData == Null) {
            $aboutData = new About();
            $oldFilePathCV = Null;
            $oldFilePathPhoto = Null;
        } else {
            $oldFilePathCV = $aboutData->getFileNameCv();
            $oldFilePathPhoto = $aboutData->getFileNamePhoto();
        }
        $form = $this->createForm(AboutType::class, $aboutData);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $pictureFileName = $form->get('fileNamePhoto')->getData();
            $cvFileName = $form->get('fileNameCv')->getData();
            if ($pictureFileName || $cvFileName) {

                try {
                    if ($aboutData == Null) {
                        $newFileNameCv = $imageUploadService->uploadNewImage($cvFileName);
                        $newFileNamePhoto = $imageUploadService->uploadNewImage($pictureFileName);
                    } else {
                        $newFileNameCv = $imageUploadService->uploadEditImage($cvFileName, $oldFilePathCV);
                        $newFileNamePhoto = $imageUploadService->uploadEditImage($pictureFileName, $oldFilePathPhoto);
                    }

                    $aboutData->setFileNameCv($newFileNameCv);
                    $aboutData->setFileNamePhoto($newFileNamePhoto);
                    $em->persist($aboutData);
                    $em->flush();
                    $this->addFlash('success', 'Dodano dane');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Wystąpił nieoczekiwany błąd zdjęcia');
                }
            }
        }
        return $this->render('about/index.html.twig', [
            'aboutForm' => $form->createView(),
            'aboutData' => $aboutData,
        ]);
    }
}
