<?php

namespace App\Controller;

use App\Ecomail\Order\EcomailOrder;
use App\Ecomail\Order\EcomailPaymentAccepted;
use App\Entity\Orders\Order;
use App\Entity\Orders\OrderItem;
use App\Entity\Orders\OrderState;
use App\Entity\Team\Team;
use App\Fakturoid\FakturoidInvoice;
use App\Fakturoid\FakturoidSubject;
use App\Form\Order\OrderType;
use App\Repository\Enum\DeliveryRepository;
use App\Repository\Enum\OrderStateRepository;
use App\Repository\Enum\PaymentMethodRepository;
use App\Repository\Games\GameRepository;
use App\Repository\Orders\OrderRepository;
use App\Repository\Team\TeamRepository;
use App\Stripe\StripeCheckoutSession;
use App\Stripe\StripeCustomer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\ByteString;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV1;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class CheckoutController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private Breadcrumbs $breadcrumbs;
    private OrderRepository $orderRepository;

    public function __construct(EntityManagerInterface $entityManager, Breadcrumbs $breadcrumbs, OrderRepository $orderRepository)
    {
        $this->entityManager = $entityManager;
        $this->breadcrumbs = $breadcrumbs;
        $this->orderRepository = $orderRepository;
    }

    #[Route(path: "/cart", name: "app_cart")]
    public function cart(RequestStack $requestStack): Response
    {
        $this->breadcrumbs->addItem("Hlavní strana", $this->generateUrl('app_homepage'));
        $this->breadcrumbs->addItem("Nákupní košík");

        $cart = $requestStack->getSession()->get("cart", null);

        return $this->render("cart/cart.html.twig", [
            "cart" => $cart
        ]);
    }

    #[Route(path: "/checkout-user", name: "app_checkout_user")]
    public function checkoutUser(): Response
    {
        $this->breadcrumbs->addItem("Hlavní strana", $this->generateUrl('app_homepage'));
        $this->breadcrumbs->addItem("Nákupní košík", $this->generateUrl('app_cart'));
        $this->breadcrumbs->addItem("Přihlásit se");

        return $this->render("checkout/checkout-user.html.twig");
    }

    #[Route(path: "/checkout", name: "app_checkout")]
    public function checkout(Request $request, RequestStack $requestStack, DeliveryRepository $deliveryRepository, PaymentMethodRepository $paymentMethodRepository,
                             EcomailOrder $ecomailOrder, FakturoidSubject $fakturoidSubject, FakturoidInvoice $fakturoidInvoice, GameRepository $gameRepository,
                             OrderStateRepository $orderStateRepository, TeamRepository $teamRepository): Response
    {
        $this->breadcrumbs->addItem("Hlavní strana", $this->generateUrl('app_homepage'));
        $this->breadcrumbs->addItem("Nákupní košík", $this->generateUrl('app_cart'));
        $this->breadcrumbs->addItem("Informace");

        // cart
        $cart = $requestStack->getSession()->get("cart", null);
        if ($cart === null)
        {
            return $this->redirectToRoute('app_cart');
        }

        // create new order
        $order = new Order();
        $order->setUuid(new UuidV1());
        $order->setUser($this->getUser());

        // create form
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            // total price
            $totalPrice = 0;
            foreach ($cart as $item)
            {
                $totalPrice += ($item["amount"] * $item["price"]);
            }

            // process form
            $order->setDeliveryInfo();
            $order->setPrice(($totalPrice + $order->getDelivery()->getPrice() + $order->getPaymentMethod()->getPrice()));

            foreach ($cart as $item)
            {
                $game = $gameRepository->find($item["object"]->getId());

                $orderItem = new OrderItem();
                $orderItem->setGame($game);
                $orderItem->setAmount($item["amount"]);
                $orderItem->setPrice($item["amount"] + $item["price"]);
                $order->addItem($orderItem);

                // create teams - random codes
                if ($game->isCreateTeamAutomatically())
                {
                    for ($i = 0; $i < $item["amount"]; $i++)
                    {
                        $ok = false;

                        do
                        {
                            $randomCode = ByteString::fromRandom(6, implode('', range('A', 'Z')))->toString();
                            $check = $teamRepository->findOneBy(["code" => $randomCode]);
                            if ($check === null)
                            {
                                $ok = true;
                            }
                        } while (!$ok);

                        $orderTeam = new Team();
                        $orderTeam->setCode($randomCode);
                        $order->addTeam($orderTeam);
                    }
                }
            }

            $this->entityManager->persist($order);
            $this->entityManager->flush();

            // send confirmation e-mail
            $ecomailOrder->sendEmail($order);
            $fakturoidId = $fakturoidSubject->getSubject($order, $this->getUser());
            $fakturoidInvoice->createInvoice($order, $fakturoidId);

            // create order state
            $orderState = new OrderState();
            $orderState->setOrderState($orderStateRepository->find(\App\Entity\Enum\OrderState::ORDER_STATE_NEW));
            $order->addState($orderState);
            $this->entityManager->persist($order);
            $this->entityManager->persist($orderState);
            $this->entityManager->flush();

            // remove data from cart
            $requestStack->getSession()->clear();

            return $this->redirectToRoute('app_checkout_payment', ["id" => $order->getUuid()->toRfc4122()]);
        }

        $delivery = $deliveryRepository->findAll();
        $paymentMethod = $paymentMethodRepository->findBy([], ["ordering" => "ASC"]);

        return $this->render("checkout/checkout.html.twig", [
            "cart" => $cart,
            "form" => $form->createView(),
            "delivery" => $delivery,
            "paymentMethod" => $paymentMethod
        ]);
    }

    private function getOrderByUuid(string $uuid): Order
    {
        if (!Uuid::isValid($uuid))
        {
            throw new \Exception("Code not found.", 404);
        }

        $order = $this->orderRepository->findOneBy(['uuid' => Uuid::fromString($uuid)->toBinary()]);
        if ($order === null)
        {
            throw new \Exception("Order not found.", 404);
        }

        return $order;
    }

    #[Route(path: "/checkout-payment/{id}", name: "app_checkout_payment")]
    public function payment(Request $request, $id, StripeCustomer $stripeCustomer, StripeCheckoutSession $stripeCheckoutSession): Response
    {
        $order = $this->getOrderByUuid($id);

        if ($order->getPaymentMethod()->getSlug() !== "kartou-online" && $request->get('pay', false) === false)
        {
            return $this->redirectToRoute("app_checkout_select_delivery", [
                "id" => $id
            ]);
        }

        $customerId = $stripeCustomer->getCustomerId($order, $this->getUser());
        $sessionUrl = $stripeCheckoutSession->createCheckoutSession($order, $customerId);
        return $this->redirect($sessionUrl);
    }

    #[Route(path: "/checkout-payment-successful/{id}", name: "app_checkout_payment_successful")]
    public function paymentSuccessful(string $id, FakturoidInvoice $fakturoidInvoice, EcomailPaymentAccepted $ecomailPaymentAccepted, OrderStateRepository $orderStateRepository): Response
    {
        $order = $this->getOrderByUuid($id);

        // update order
        $order->setPayedAt(new \DateTime());
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        // update invoice
        $fakturoidInvoice->setInvoicePaid($order->getFakturoidId());

        // send email
        $ecomailPaymentAccepted->sendEmail($order);

        // create new order state
        $orderState = new OrderState();
        $orderState->setOrderState($orderStateRepository->find(\App\Entity\Enum\OrderState::ORDER_STATE_IN_PROCESS));
        $order->addState($orderState);
        $this->entityManager->persist($order);
        $this->entityManager->persist($orderState);
        $this->entityManager->flush();

        // redirect to select delivery
        return $this->redirectToRoute("app_checkout_select_delivery", [
            "id" => $id
        ]);
    }

    #[Route(path: '/select-delivery-place/{id}', name: 'app_checkout_select_delivery')]
    public function selectDeliveryPlace(Request $request, $id): Response
    {
        $order = $this->getOrderByUuid($id);

        if ($order->getDelivery()->getSlug() !== "zasilkovna-na-vydejni-misto" || $order->getZasilkovnaDeliveryPlace() !== null)
        {
            return $this->redirectToRoute("app_order", [
                "id" => $id
            ]);
        }

        if ($request->get('placeId', null) !== null)
        {
            $packetaId = $request->get('placeId');
            $packetaName = $request->get('placeName');
            $order->setZasilkovnaDeliveryPlace($packetaId);
            $order->setZasilkovnaDeliveryPlaceText($packetaName);
            $this->entityManager->persist($order);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_order', ["id" => $id]);
        }

        return $this->render("checkout/select-delivery.html.twig", [
            "order" => $order,
            "zasilkovnaApi" => $this->getParameter("zasilkovna.key"),
        ]);
    }
}