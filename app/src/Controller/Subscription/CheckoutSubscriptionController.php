<?php

namespace App\Controller\Subscription;

use Psr\Log\LoggerInterface;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Symfony\Component\Routing\Generator\UrlGenerator;

#[Route('/subscription')]
class CheckoutSubscriptionController extends AbstractController
{

    public function __construct()
    {
        Stripe::setApiKey('sk_test_51KxngZDUppKYGFFMwSQ6vrRxE3jHwBor5MzPQ5z53ahJdaWU0GAtoSXfF2zHGIIMxYr86iw5ClkxEW4NDgZYFWz400fuhYRXrW');

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

        switch ($type) {

            case 'checkout.session.completed':
                $email = (new Email())
                    // email address as a simple string
                    ->from('fabien@example.com')
                    ->to('suce@admin.fr');
                $email->text("Bienvenue mon chef !");
                $mailer->send($email);
                break;
            case 'invoice.paid':
                $email = (new Email())
                    // email address as a simple string
                    ->from('fabien@example.com')
                    ->to('suce@admin.fr');
                $email->text("C'est payé chef !");
                $mailer->send($email);
                break;
            case 'invoice.payment_failed':
                $email = (new Email())
                    // email address as a simple string
                    ->from('fabien@example.com')
                    ->to('suce@admin.fr');
                $email->text("C'est pas payé chef !");
                $mailer->send($email);
                break;
            case 'customer.subscription.updated':
                $email = (new Email())
                    // email address as a simple string
                    ->from('fabien@example.com')
                    ->to('suce@admin.fr');
                $email->text($object['cancel_at_period_end']?"Nous avons bien pris en compte votre annulation":"Votre plan a bien été mis à jour");
                $mailer->send($email);
                break;
            default:
                // Unhandled event type
        }

        return $this->json([ 'status' => 'success' ]);
    }
}
