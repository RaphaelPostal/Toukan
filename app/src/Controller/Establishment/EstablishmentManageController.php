<?php

namespace App\Controller\Establishment;

use App\Entity\Establishment;
use App\Entity\User;
use App\Form\EstablishmentImageType;
use App\Form\EstablishmentInfoType;
use App\Form\EstablishmentPasswordType;
use App\Repository\EstablishmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\UX\Turbo\TurboBundle;

#[Route('/establishment')]
class EstablishmentManageController extends AbstractController
{
    #[Route('/manage', name: 'app_establishment_manage')]
    public function index(): Response
    {
        //$establishment = $this->getUser()->getEstablishment();

        $establishment =$this->getDoctrine()->getRepository(Establishment::class)->find(1);
        $user = $this->getDoctrine()->getRepository(User::class)->find(1);

        return $this->render('establishment/information/index.html.twig', [
            'user' => $user,
            'establishment' => $establishment,
        ]);
    }

    #[Route('/information-edit', name: 'app_establishment_information_edit', methods: ['GET', 'POST'])]
    public function editInformation(Request $request, EstablishmentRepository $establishmentRepository, EntityManagerInterface $entityManager): Response
    {
//        $establishment = $this->getUser()->getEstablishment();
//        $user = $this->getUser();

        $user = $this->getDoctrine()->getRepository(User::class)->find(1);
        $establishment = $establishmentRepository->find(1);
        $form = $this->createForm(EstablishmentInfoType::class, $establishment, [
            'action' => $this->generateUrl('app_establishment_information_edit'),
            'email' => $user->getEmail(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $email = $form->get('email')->getData();
            $user->setEmail($email);
            $establishmentRepository->add($establishment);

            $entityManager->flush($user);

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $this->addFlash('success', 'Informations sauvegardées !');
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                return $this->render('establishment/stream/information.stream.html.twig', ['establishment' => $establishment, 'user' => $user]);
            }
            return $this->redirectToRoute('app_establishment_manage', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('establishment/information/edit.html.twig', [
            'establishment' => $establishment,
            'form' => $form,
        ]);

    }

    #[Route('/password-edit', name: 'app_establishment_password_edit', methods: ['GET', 'POST'])]
    public function editPassword(
        Request $request,
        EstablishmentRepository $establishmentRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
//        $establishment = $this->getUser()->getEstablishment();
//        $user = $this->getUser();

        $user = $this->getDoctrine()->getRepository(User::class)->find(1);
        $establishment = $establishmentRepository->find(1);

        $form = $this->createForm(EstablishmentPasswordType::class, null, [
            'action' => $this->generateUrl('app_establishment_password_edit'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $password = $form->get('password')->getData();

                if (!$passwordHasher->isPasswordValid($user, $password)) {
                    $this->addFlash('error', "Le mot de passe actuel n'est pas correct. Si vous ne vous en souvenez plus, vous pouvez toujours le réinitialiser.");
                    $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                    return $this->render('establishment/stream/information.stream.html.twig', ['establishment' => $establishment, 'user' => $user]);
                }

                $newPassword = $form->get('newPassword')->getData();
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));

                $entityManager->flush($user);

                $this->addFlash('success', 'Mot de passe sauvegardé !');
            } else {
                $this->addFlash('error', "Les mots de passes ne correspondent pas.");
            }

            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
            return $this->render('establishment/stream/information.stream.html.twig', ['establishment' => $establishment, 'user' => $user]);
        }

        return $this->renderForm('establishment/information/edit.html.twig', [
            'establishment' => $establishment,
            'form' => $form,
        ]);

    }

    #[Route('/image-edit', name: 'app_establishment_image_edit', methods: ['GET', 'POST'])]
    public function editImage(
        Request $request,
        EstablishmentRepository $establishmentRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        SluggerInterface $slugger
    ): Response
    {
        $establishment = $this->getUser()->getEstablishment();
        $user = $this->getUser();
        $form = $this->createForm(EstablishmentImageType::class, null, [
            'action' => $this->generateUrl('app_establishment_image_edit'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $imageFile = $form->get('image')->getData();

                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                    // Move the file to the directory where brochures are stored
                    try {
                        $imageFile->move(
                            $this->getParameter('images_directory').'/'.$establishment->getId(),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        dump('impossible de déplacer le fichier');
                    }

                    // updates the 'brochureFilename' property to store the PDF file name
                    // instead of its contents
                    $establishment->setImage($newFilename);
                }

                $entityManager->flush();

                $this->addFlash('success', 'Image sauvegardée !');

                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                return $this->render('establishment/stream/information.stream.html.twig', ['establishment' => $establishment, 'user' => $user ]);
            }

            return $this->renderForm('establishment/information/edit.html.twig', [
                'establishment' => $establishment,
                'form' => $form,
            ]);

        }

        return $this->renderForm('establishment/information/edit.html.twig', [
            'establishment' => $establishment,
            'form' => $form,
        ]);
    }
}
