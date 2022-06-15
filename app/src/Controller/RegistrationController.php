<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\Establishment;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Customer;
use Stripe\Stripe;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{

    private readonly Request $request;

    public function __construct(
        private readonly EmailVerifier          $emailVerifier,
        private readonly RequestStack           $requestStack,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface    $translator,
    )
    {
        $this->request = $this->requestStack->getCurrentRequest();
    }

    #[Route('/register', name: 'register')]
    public function register(UserPasswordHasherInterface $userPasswordHasherInterface): Response
    {
        $address = [];
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address['street'] = $form->get('street')->getData();
            $address['city'] = $form->get('city')->getData();
            $address['zipcode'] = $form->get('zipcode')->getData();

            $establishment = new Establishment();

            $establishment->setUser($user);
            $user->setEstablishment($establishment);

            $establishment->setName($form->get('name')->getData());
            $establishment->setType($form->get('type')->getData());
            $establishment->setAddress($address);
            $establishment->setPhone($form->get('phone')->getData());

            // encode the plain password
            $user->setPassword(
            $userPasswordHasherInterface->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $card = new Card();
            $card->setEstablishment($establishment);
            
            $this->entityManager->persist($user);
            $this->entityManager->persist($establishment);
            $this->entityManager->persist($card);
            $this->entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('contact@toukan.fr', 'Toukan'))
                    ->to($user->getEmail())
                    ->subject($this->translator->trans('subject.confirm_address', domain: 'mail'))
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
    public function verifyUserEmail(Request $request, UserRepository $repository, EntityManagerInterface $entityManager): Response
    {

        $id = $request->get('id');
        if (!$id) {
            $this->addFlash('error', $this->translator->trans('invalid_verification_link', domain: 'security'));

            return $this->redirectToRoute('login');
        }

        $user = $repository->find($id);
        if (!$user instanceof \App\Entity\User) {
            $this->addFlash('error', $this->translator->trans('user_not_find', domain: 'security'));

            return $this->redirectToRoute('login');
        }

        if ($user->isVerified()) {
            $this->addFlash('error', $this->translator->trans('already_verified_email', domain: 'security'));
            return $this->redirectToRoute('login');
        }
        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($this->request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('login');
        }

        Stripe::setApiKey($this->getParameter('stripe_api_key'));

        $newCustomer = Customer::create([
            'email' => $user->getEmail(),
            'name' => $user->getEstablishment()->getName(),
            'phone' => $user->getEstablishment()->getPhone(),
            'address' => [
                'line1' => $user->getEstablishment()->getAddress()['street'],
                'city' => $user->getEstablishment()->getAddress()['city'],
                'postal_code' => $user->getEstablishment()->getAddress()['zipcode'],
                'state' => '',
                'country' => 'FR',
                //TODO : add country and state to the form
            ],
        ]);

        $user->setSessionId($newCustomer->id);
        $entityManager->flush();

        $this->addFlash('success', $this->translator->trans('verified_email', domain: 'security'));

        return $this->redirectToRoute('login');
    }
}
