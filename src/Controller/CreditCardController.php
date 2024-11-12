<?php
namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Form\CreditCardCollectionFormType;
use App\Form\CreditCardFormType;
use App\Entity\CreditCard;
use Doctrine\ORM\EntityManagerInterface;

class CreditCardController extends AbstractController
{   
    #[Route("/creditcard/index", name: "show_form_credit_card")]
    public function showForm(EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $user = $this->getUser();
        if(!$user){
            $this->addFlash('info', 'Vous devez vous connecter pour pouvoir finaliser votre commande');
            return $this->redirectToRoute('app_login');
        }
        
        $cardsUser = $entityManager->getRepository(CreditCard::class)->findBy(['ofUser' => $this->getUser()], ["id" => "ASC"]);

        $newCreditCard = new CreditCard();
        $form = $this->createForm(CreditCardFormType::class, $newCreditCard);

        return $this->render('creditCard/creditCard.html.twig', [
            'form' => $form->createView(),
            'cardsUser' => $cardsUser,
        ]);
    }

    #[Route("", name: "add_credit_card", methods: ["POST"])]
    public function addCreditCard(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $newCreditCard = new CreditCard();
        $form = $this->createForm(CreditCardFormType::class, $newCreditCard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newCreditCard->setOfUser($this->getUser());

            $entityManager->persist($newCreditCard);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'cardData' => [
                    'id' => $newCreditCard->getId(),
                    'number' => $newCreditCard->getNumber(),
                    'expirationDate' => $newCreditCard->getExpirationDate(),
                    'cvv' => $newCreditCard->getCvv(),
                ]
            ]);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[$error->getOrigin()->getName()] = $error->getMessage();
        }

        return new JsonResponse([
            'success' => false,
            'errors' => $errors
        ]);
    }

    
    #[Route(path: "/creditcard/{id}/delete", name: "credit_card_delete", methods: ["POST"])]
    public function delete(Request $request, CreditCard $creditCardID, EntityManagerInterface $entityManager): Response
    {
        $creditCard = $entityManager->getRepository(CreditCard::class)->find($creditCardID);

        $entityManager->remove($creditCard);
        $entityManager->flush();
        return new JsonResponse(['status' => 'success']);
    }

}