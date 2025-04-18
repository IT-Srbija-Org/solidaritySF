<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Repository\UserDonorRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/ponovna-verifikacija-email', name: 'resend_verification')]
    public function resendVerification(Request $request, UserRepository $userRepository): Response
    {
        $email = $request->query->get('email');
        if (!$email) {
            $this->addFlash('error', 'Email nije prosleđen.');

            return $this->redirectToRoute('login');
        }

        $user = $userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            $this->addFlash('error', 'Korisnik sa ovom email adresom ne postoji.');

            return $this->redirectToRoute('login');
        }

        if ($user->isEmailVerified()) {
            $this->addFlash('success', 'Vaš nalog je već verifikovan. Možete se prijaviti.');

            return $this->redirectToRoute('login');
        }

        $session = $request->getSession();
        $lastResendKey = 'last_verification_resend_'.$email;
        $lastResend = $session->get($lastResendKey);
        $now = new \DateTime();

        if ($lastResend) {
            $minutesSinceLastResend = ($now->getTimestamp() - $lastResend) / 60;
            if ($minutesSinceLastResend < 5) {
                $this->addFlash(
                    'error',
                    sprintf(
                        'Molimo sačekajte još %d minuta pre nego što ponovo pošaljete verifikacioni email.',
                        ceil(5 - $minutesSinceLastResend)
                    )
                );

                return $this->redirectToRoute('login');
            }
        }

        $userRepository->sendVerificationLink($user, null);
        $session->set($lastResendKey, $now->getTimestamp());

        $this->addFlash('success', 'Verifikacioni email je ponovo poslat na vašu adresu. Molimo proverite vaš inbox.');

        return $this->redirectToRoute('login');
    }

    #[Route('/registracija', name: 'register')]
    public function register(Request $request, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $userRepository->sendVerificationLink($user, null);

            return $this->redirectToRoute('verify_email_send');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/verifikacija-email', name: 'verify_email_send')]
    public function verifyEmailSend(): Response
    {
        return $this->render('registration/verify_email_send.html.twig');
    }

    #[Route('/uspesna-verifikacija-emaila', name: 'verify_email_success')]
    public function verifyEmailSuccess(): Response
    {
        return $this->render('registration/verify_email_success.html.twig');
    }

    #[Route('/email-verifikacija', name: 'verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository, UserDonorRepository $userDonorRepository, Security $security): Response
    {
        $userId = $request->get('id');
        if (!$userId) {
            throw new AuthenticationCredentialsNotFoundException();
        }

        $user = $userRepository->find($userId);
        if (!$user) {
            throw new AuthenticationCredentialsNotFoundException();
        }

        try {
            // Check if user validation token valid
            $this->emailVerifier->handleEmailConfirmation($request, $user);

            // Login user
            $security->login($user, 'form_login');

            $action = $request->get('action');
            if ('donor' == $action) {
                $userDonorRepository->sendSuccessEmail($user);

                return $this->redirectToRoute('donor_success');
            }

            if ('delegate' == $action) {
                return $this->redirectToRoute('delegate_request_success');
            }
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', $exception->getReason());

            return $this->redirectToRoute('register');
        }

        return $this->redirectToRoute('verify_email_success');
    }
}
