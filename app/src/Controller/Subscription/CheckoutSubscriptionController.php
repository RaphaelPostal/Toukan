<?php

namespace App\Controller\Subscription;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\Product;
use Stripe\Subscription;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/subscription')]
class CheckoutSubscriptionController extends AbstractController
{

    public function __construct(
        private TranslatorInterface $translator,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        )
    {}

    #[Route('/test', name: 'test')]
    public function test()
    {
        Stripe::setApiKey($this->getParameter('stripe_api_key'));

        $customer = Customer::retrieve($this->getUser()->getSessionId(), ['expand' => ['subscriptions']]);
//        $customer = Customer::create([
//            'email' => 'admin@admin.fr',
//            'name' => 'admin',
//            'phone' => '+32456456789',
//            'address' => [
//                'line1' => '1 Main St',
//                'city' => 'San Francisco',
//                'state' => 'CA',
//                'postal_code' => '94105',
//                'country' => 'US',
//            ],
//        ]);

        dd($customer);
        return $this->render('subscription/test.html.twig');
    }

    #[Route('/pricing', name: 'app_subscription_pricing')]
    public function getPricing()
    {
        Stripe::setApiKey($this->getParameter('stripe_api_key'));

        $prices = Price::all([
            'active' => true,
            ]);
        dd($prices);
    }

    #[Route('/checkout', name: 'app_subscription_checkout')]
    public function index(): Response
    {
        Stripe::setApiKey($this->getParameter('stripe_api_key'));

        $customer = Customer::retrieve($this->getUser()->getSessionId());

        $session = Session::create([
            'customer' => $customer->id,
            'mode'=>'subscription',
            'line_items' => [
                [
                    'price' => 'price_1KxpEDDUppKYGFFMGRO49zAm',
//                    'price' => 'price_1KyCGSDUppKYGFFMxk6L02z0',
                    'quantity' => 1,
                ],
            ],
            'success_url' => $this->generateUrl('app_subscription_checkout_success', referenceType: UrlGenerator::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('app_subscription_checkout_cancel', referenceType:  UrlGenerator::ABSOLUTE_URL),
        ]);

        return $this->redirect($session->url);
    }

    #[Route('/portal', name:'app_establishment_portal')]
    public function handlePortal(Request $request)
    {
        Stripe::setApiKey($this->getParameter('stripe_api_key'));

        try {
            $customer = Customer::retrieve($this->getUser()->getSessionId());
            $return_url = $this->generateUrl('establishment_profile', referenceType: UrlGenerator::ABSOLUTE_URL);

            // Authenticate your user.
            $session = \Stripe\BillingPortal\Session::create([
                'customer' => $customer,
                'return_url' => $return_url,
            ]);

            return $this->redirect($session->url);
        } catch (ApiErrorException $e) {
            dd($e);
        }
    }

    #[Route('/checkout/success', name: 'app_subscription_checkout_success')]
    public function successSubscription(): Response
    {
        return $this->render('subscription/success.html.twig');
    }

    #[Route('/checkout/cancelled', name: 'app_subscription_checkout_cancel')]
    public function cancelSubscription(): Response
    {
        return $this->render('subscription/cancel.html.twig');
    }

    #[Route('/webhook', name: 'app_subscription_webhook')]
    public function handleWebhook(Request $request, LoggerInterface $logger, MailerInterface $mailer): Response
    {
        Stripe::setApiKey($this->getParameter('stripe_api_key'));

        $event = $request->getContent();
        // Parse the message body and check the signature
        $webhookSecret = "";
        if ($webhookSecret) {
            try {
                $event = Webhook::constructEvent(
                    $event,
                    $request->headers->get('stripe-signature'),
                    $webhookSecret
                );
            } catch (\Exception $e) {
                return $this->json([ 'error' => $e->getMessage() ])->setStatusCode(403);
            }
        } else {
            $event = $request->toArray();
        }
        $type = $event['type'];
        $object = $event['data']['object'];

        //get stripe customer from stripe id
        if (!array_key_exists('customer', $object)) {
            return $this->json([ 'error' => 'No customer found' ])->setStatusCode(403);
        }

        $customer = Customer::retrieve($object['customer']);

        switch ($type) {

            case 'checkout.session.completed':
                $mail_subject = $this->translator->trans('subject.received_payment', domain: 'mail');
                $mail_template = 'mail_template/subscription/welcome.html.twig';
                break;
//            case 'invoice.created':
//                $mail_subject = null;
//                $mail_template = null;
//                break;
            case 'invoice.paid':
                $mail_subject = $this->translator->trans('subject.paid_invoice', domain: 'mail');
                $mail_template = 'invoice/subscription_checkout_success.html.twig';
                break;
            case 'invoice.payment_failed':
                $mail_subject = $this->translator->trans('subject.failed_payment', domain: 'mail');
                $mail_template = 'invoice/subscription_checkout_failed.html.twig';
                break;
//            case 'invoice.payment_action_required':
//                $mail_subject = null;
//                $mail_template = null;
//                break;
            case 'customer.subscription.updated':
                $this->setUserSubscription($customer);
                $mail_subject = $this->translator->trans('subject.subscription_updated', domain: 'mail');
                $mail_template = $object['cancel_at_period_end'] ? 'mail_template/subscription/cancelled.html.twig' : 'mail_template/subscription/updated.html.twig';
                break;
            case 'customer.subscription.deleted':
                $this->setUserSubscription($customer);
                $mail_template = null;
                $mail_subject = null;
                break;
            default:
                $mail_template = null;
                $mail_subject = null;
        }

        if ($mail_template){
            $email = (new Email())
                // email address as a simple string
                ->from(new Address('contact@toukan-app.fr', 'Toukan App'))
                ->to($customer->email)
                ->subject($mail_subject)
                ->html($this->renderView($mail_template, [
                    'object' => $object,
                    'user' => $customer,
                ]));

            $mailer->send($email);
        }

        return $this->json([ 'status' => 'success' ]);
    }

    private function setUserSubscription(Customer $customer)
    {
        Stripe::setApiKey($this->getParameter('stripe_api_key'));

        $customer = Customer::retrieve($customer->id);

        $subscription = Subscription::all(['customer' => $customer->id, 'status' => 'active']);
        $toukanProducts = Product::all([ 'active' => true]);

        $subscriptionsProduct = array_map(function ($subscription) {
            return $subscription->plan->product;
        }, $subscription->data);

        $allowedToukanProducts = array_filter($toukanProducts->data, function ($product){
            return $product->metadata->allow_app_usage;
        });

        $allowedToukanProductsId = array_map(function ($product){
            return $product->id;
        }, $allowedToukanProducts);

        $isUserAllowedToUseToukan = array_intersect(array_values($allowedToukanProductsId), array_values($subscriptionsProduct));

        $this->userRepository->findOneBy(['session_id' => $customer->id])->setSubscriptionActive((bool)$isUserAllowedToUseToukan);
        $this->entityManager->flush();
    }
}
