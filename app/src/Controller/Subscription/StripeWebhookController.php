<?php

namespace App\Controller\Subscription;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Customer;
use Stripe\Product;
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

#[Route('/subscription')]
class StripeWebhookController extends AbstractController
{

    public function __construct(
        private TranslatorInterface $translator,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        )
    {}

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
