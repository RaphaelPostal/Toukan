<?php

namespace App\Controller;

use App\Entity\Establishment;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{

    private Request $request;

    public function __construct(
        private EmailVerifier $emailVerifier,
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
    )
    {
        $this->request = $this->requestStack->getCurrentRequest();
    }

    #[Route('/creation-establishment', name: 'creation-establishment')]
    public function register(UserPasswordHasherInterface $userPasswordHasherInterface): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {

            $establishment = new Establishment();

            $establishment->setUser($user);

            $establishment->setName($form->get('name')->getData());
            $establishment->setType($form->get('type')->getData());
            $establishment->setAddress($form->get('address')->getData());
            $establishment->setPhone($form->get('phone')->getData());

            // encode the plain password
            $user->setPassword(
            $userPasswordHasherInterface->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $this->entityManager->persist($user);
            $this->entityManager->persist($establishment);
            $this->entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('contact@toukan.fr', 'Toukan'))
                    ->to($user->getEmail())
                    ->subject('Confirmez votre adresse email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            // do anything else you need here, like send an email

            return $this->redirectToRoute('login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $repository): Response
    {

        $id = $request->get('id');
        if (!$id) {
            $this->addFlash('error', 'Le lien de vérification est invalide');

            return $this->redirectToRoute('login');
        }

        $user = $repository->find($id);
        if (!$user) {
            $this->addFlash('error', 'Aucun utilisateur trouvé');

            return $this->redirectToRoute('login');
        }
        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($this->request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('login');
        }

        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('login');
    }
}
