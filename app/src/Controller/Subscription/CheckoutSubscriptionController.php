<?php

namespace App\Controller\Subscription;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\Stripe;
use Stripe\Subscription;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/establishment/subscription')]
class CheckoutSubscriptionController extends AbstractController
{

    public function __construct(
        private readonly TranslatorInterface    $translator,
        private readonly UserRepository         $userRepository,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

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

    #[Route('/upgrade/{priceId}', name: 'app_subscription_upgrade')]
    public function upgradeSubscription($priceId){
        if (!$this->getUser()->isSubscriptionActive()) {
//            return $this->redirectToRoute('establishment_dashboard');
        }
        //Upgrade user's subscription on stripe
        Stripe::setApiKey($this->getParameter('stripe_api_key'));

        $subscription = Subscription::retrieve($this->getUser()->getSubscriptionId());

        Subscription::update($subscription->id, [
            'cancel_at_period_end' => false,
            'proration_behavior' => 'create_prorations',
            'items' => [
                [
                    'id' => $subscription->items->data[0]->id,
                    'price' => $priceId,
                ],
            ],
        ]);

        return $this->redirectToRoute('establishment_dashboard');
    }

    #[Route('/pricing', name: 'app_subscription_pricing')]
    public function getPricing()
    {
        if ($this->getUser()->isSubscriptionActive()) {
            $this->addFlash('warning', $this->translator->trans('subscription.already_subscribed', domain: 'alert'));
//            return $this->redirectToRoute('establishment_dashboard');
        }

        Stripe::setApiKey($this->getParameter('stripe_api_key'));

        $prices = Price::all(['active' => true, 'expand' => ['data.product']]);

        $prices = array_values(array_filter($prices->data, fn($price) => $price->product->metadata->allow_app_usage));

        return $this->render('establishment/pricing/index.html.twig', [
            'prices' => $prices,
        ]);
    }

    #[Route('/checkout/{priceId}', name: 'app_subscription_checkout')]
    public function index($priceId): Response
    {
        Stripe::setApiKey($this->getParameter('stripe_api_key'));

        $customer = Customer::retrieve($this->getUser()->getSessionId());

        $session = Session::create([
            'customer' => $customer->id,
            'mode'=>'subscription',
            'line_items' => [
                [
                    'price' => $priceId,
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

    #[Route('/checkout/success', name: 'app_subscription_checkout_success', priority: 2)]
    public function successSubscription(): Response
    {
        return $this->render('subscription/success.html.twig');
    }

    #[Route('/checkout/cancelled', name: 'app_subscription_checkout_cancel', priority: 2)]
    public function cancelSubscription(): Response
    {
        return $this->render('subscription/cancel.html.twig');
    }
}
