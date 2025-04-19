<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Service\IpsQrCodeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class QrCodeController extends AbstractController
{
    #[Route('/profil/qr-kod/{id}', name: 'transaction_qr', requirements: ['id' => '\d+'])]
    public function transactionQr(Transaction $transaction, IpsQrCodeService $qrCodeService): Response
    {
        $user = $this->getUser();
        if ($transaction->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $paymentData = [
            'bankAccountNumber' => $transaction->getAccountNumber(),
            'payeeName' => $transaction->getDamagedEducator()->getName(),
            'amount' => number_format($transaction->getAmount(), 2, ',', ''),
            'payerName' => $user->getFullName(),
            'paymentCode' => '289',
            'paymentPurpose' => 'Transakcija po nalogu građana',
        ];

        $qrString = $qrCodeService->createIpsQrString($paymentData);
        $qrDataUri = $qrCodeService->getQrCodeDataUri($qrString);

        return $this->render('profile/qr_modal_content.html.twig', [
            'qrDataUri' => $qrDataUri,
            'transaction' => $transaction,
        ]);
    }
}
