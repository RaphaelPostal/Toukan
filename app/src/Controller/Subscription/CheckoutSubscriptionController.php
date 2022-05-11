<?php

namespace App\Controller\Subscription;

use Psr\Log\LoggerInterface;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Symfony\Component\Routing\Generator\UrlGenerator;

#[Route('/subscription')]
class CheckoutSubscriptionController extends AbstractController
{

    public function __construct(RequestStack $requestStack, LoggerInterface $logger, MailerInterface $mailer)
    {
        Stripe::setApiKey('sk_test_51KxngZDUppKYGFFMwSQ6vrRxE3jHwBor5MzPQ5z53ahJdaWU0GAtoSXfF2zHGIIMxYr86iw5ClkxEW4NDgZYFWz400fuhYRXrW');
    }

    #[Route('/test', name: 'test')]
    public function test()
    {
        $customer = Customer::retrieve('cus_LfXMaZEnbidJzt');
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

        dd($customer->name);
        return $this->render('subscription/test.html.twig');
    }

    #[Route('/pricing', name: 'app_subscription_pricing')]
    public function getPricing()
    {
        $prices = Price::all([
            'active' => true,
            ]);
        dd($prices);
    }

    #[Route('/checkout', name: 'app_subscription_checkout')]
    public function index(): Response
    {
        $stripeCheckoutSession = new Session;

        $customer = Session::retrieve('cs_test_a1oeQG0YPxWLYfNsihtwVjaBja49mvC9pA5aWxPalKNlEuwk6IoNHLJXLY');

        $session = $stripeCheckoutSession->create([
            'customer' => $customer->customer,
            'mode'=>'subscription',
            'line_items' => [
                [
                    'price' => 'price_1KxpEDDUppKYGFFMGRO49zAm',
//                    'price' => 'price_1KyCGSDUppKYGFFMxk6L02z0',
                    'quantity' => 1,
                ],
            ],
            'success_url' => $this->generateUrl('app_customer_portal', referenceType: UrlGenerator::ABSOLUTE_URL)."?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url' => $this->generateUrl('app_subscription_checkout_cancel', referenceType:  UrlGenerator::ABSOLUTE_URL),
        ]);

        return $this->redirect($session->url);
    }

    #[Route('/portal', name:'app_customer_portal')]
    public function handlePortal(Request $request)
    {
        $sessionId = $request->query->get('session_id');
        try {
            $checkout_session = Session::retrieve($sessionId);
            $return_url = $this->generateUrl('home', referenceType: UrlGenerator::ABSOLUTE_URL);

            // Authenticate your user.
            $session = \Stripe\BillingPortal\Session::create([
                'customer' => $checkout_session->customer,
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
        if (array_key_exists('customer', $object)) {
            $customer = Customer::retrieve($object['customer']);
        }

        switch ($type) {

            case 'checkout.session.completed':
                $mail_template = 'mail_template/subscription/welcome.html.twig';
                break;
            case 'invoice.paid':
                $mail_template = 'invoice/subscription_checkout_success.html.twig';
                break;
            case 'invoice.payment_failed':
                $mail_template = 'invoice/subscription_checkout_failed.html.twig';
                break;
            case 'customer.subscription.updated':
                $mail_template = $object['cancel_at_period_end'] ? 'mail_template/subscription/cancelled.html.twig' : 'mail_template/subscription/updated.html.twig';
                break;
            default:
                $mail_template = null;
        }

        if ($mail_template){
            $email = (new Email())
                // email address as a simple string
                ->from('contact@toukan-app.fr')
                ->to('admin@admin.fr')
                ->html($this->renderView($mail_template, [
                    'object' => $object,
//                    'user' => $this->getUser(),
                ]));

            $mailer->send($email);
        }

        return $this->json([ 'status' => 'success' ]);
    }
}
