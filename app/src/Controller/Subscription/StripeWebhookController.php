<?php

namespace App\Controller\Subscription;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Customer;
use Stripe\Invoice;
use Stripe\Product;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/establishment/subscription')]
class StripeWebhookController extends AbstractController
{

    public function __construct(
        private readonly TranslatorInterface    $translator,
        private readonly UserRepository         $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface        $mailer,
    )
    {
    }

    #[Route('/webhook', name: 'app_subscription_webhook')]
    public function handleWebhook(Request $request): Response
    {
        Stripe::setApiKey($this->getParameter('stripe_api_key'));

        $event = $request->getContent();
        // Parse the message body and check the signature
        $webhookSecret = "";
        if ($webhookSecret !== '') {
            try {
                $event = Webhook::constructEvent(
                    $event,
                    $request->headers->get('stripe-signature'),
                    $webhookSecret
                );
            } catch (\Exception $e) {
                return $this->json(['error' => $e->getMessage()])->setStatusCode(403);
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
            case 'invoice.payment_succeeded':
                $this->setUserSubscription($customer);
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
            case 'customer.subscription.created':
                $this->removeDuplicateSubscription($customer, $object['id']);
                $mail_template = null;
                $mail_subject = null;
                break;
            case 'customer.subscription.updated':
                $this->setUserSubscription($customer);
                $mail_subject = $this->translator->trans('subject.subscription_updated', domain: 'mail');
                $mail_template = $object['cancel_at_period_end'] ? 'mail_template/subscription/cancelled.html.twig' : 'mail_template/subscription/updated.html.twig';
                break;
            case 'customer.subscription.deleted':
                $this->setUserSubscription($customer);
                $mail_subject = $this->translator->trans('subject.subscription_cancelled', domain: 'mail');
                $mail_template = 'mail_template/subscription/cancelled.html.twig';
                break;
            case 'charge.refunded':
                $mail_subject = $this->translator->trans('subject.charge_refunded', domain: 'mail');
                $mail_template = 'mail_template/subscription/cancelled.html.twig';
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

            $this->mailer->send($email);
        }

        return $this->json([ 'status' => 'success' ]);
    }

    private function removeDuplicateSubscription(Customer $customer, $subscriptionId)
    {
        $subscriptions = Subscription::all(['customer' => $customer->id, 'status' => 'active']);
        $newSubscription = Subscription::retrieve($subscriptionId);

        $sameSubscription = [];
        foreach ($subscriptions as $subscription) {
            if ($subscription->items->data[0]->price->id === $newSubscription->items->data[0]->price->id) {
                $sameSubscription[] = $subscription->id;
            }
        }

        $invoice = Invoice::retrieve($newSubscription->latest_invoice);

        if (count($sameSubscription) > 1) {
            Refund::create([
                'payment_intent' => $invoice->payment_intent,
            ]);
            $newSubscription->cancel();

            $email = (new Email())
                // email address as a simple string
                ->from(new Address('contact@toukan-app.fr', 'Toukan App'))
                ->to($customer->email)
                ->subject($this->translator->trans('subject.duplicate_subscription', domain: 'mail'))
                ->text("Nous avons détecté un abonnement en double, suppression et remboursement en cours...");

            $this->mailer->send($email);
        }
    }

    private function setUserSubscription(Customer $customer)
    {
        Stripe::setApiKey($this->getParameter('stripe_api_key'));

        $customer = Customer::retrieve($customer->id);

        $subscription = Subscription::all(['customer' => $customer->id, 'status' => 'active']);
        $toukanProducts = Product::all([ 'active' => true]);

        $subscriptionsProduct = array_map(fn($subscription) => $subscription->plan->product, $subscription->data);

        $allowedToukanProducts = array_filter($toukanProducts->data, fn($product) => $product->metadata->allow_app_usage);

        $allowedToukanProductsId = array_map(fn($product) => $product->id, $allowedToukanProducts);

        $isUserAllowedToUseToukan = array_intersect(array_values($allowedToukanProductsId), array_values($subscriptionsProduct));

        $user = $this->userRepository->findOneBy(['session_id' => $customer->id]);

        $user->setSubscriptionId($isUserAllowedToUseToukan !== [] ? $subscription->data[0]->id : null);
        $user->setSubscriptionActive((bool)$isUserAllowedToUseToukan);

        $this->entityManager->flush();
    }
}
