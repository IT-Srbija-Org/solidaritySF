<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\CloudFlareTurnstileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/logovanje', name: 'login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils, UserRepository $userRepository, CloudFlareTurnstileService $cloudFlareTurnstileService): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if ($request->isMethod('POST')) {
            
            $captchaToken = $request->getPayload()->get('cf-turnstile-response');
            $emailresend = $request->getPayload()->get('emailresend');
            
            if(!$emailresend){  
                if (!$cloudFlareTurnstileService->isValid($captchaToken)) {
                    $this->addFlash('error', 'Captcha nije validna.');
                    
                    return $this->redirectToRoute('login');
                }
                
                $email = $request->getPayload()->get('email');
                $user = $userRepository->findOneBy(['email' => $email]);
    
                if ($user && $user->isActive() && $user->isEmailVerified()) {
                    $userRepository->sendLoginLink($user);
    
                    $this->addFlash('success', 'Link za prijavu je poslat na vašu email adresu.');
    
                    return $this->redirectToRoute('login');
                }
    
                if ($user && !$user->isActive()) {
                    $this->addFlash('error', 'Korisnik sa ovom email adresom nije aktivan i ne može da se uloguje.');
    
                    return $this->redirectToRoute('login');
                }
    
                if ($user && !$user->isEmailVerified()) {
                    $this->addFlash('unverified_user', $email);
    
                    return $this->redirectToRoute('login');
                }
                
                $this->addFlash('error', 'Korisnik sa ovom email adresom ne postoji. Molimo da se registrujete.');
            
            } else {
                if (!$cloudFlareTurnstileService->isValid($captchaToken)) {
                    $this->addFlash('error', 'Captcha nije validna.');
                    $this->addFlash('unverified_user_no_alert', $emailresend);
                    return $this->redirectToRoute('login');
                }
                
                $user = $userRepository->findOneBy(['email' => $emailresend]);
                if (!$user) {
                    $this->addFlash('error', 'Korisnik sa ovom email adresom ne postoji.');
                    
                    return $this->redirectToRoute('login');
                }
                
                if ($user->isEmailVerified()) {
                    $this->addFlash('success', 'Vaš nalog je već potvrđen. Možete se prijaviti.');
                    
                    return $this->redirectToRoute('login');
                }
                
                $session = $request->getSession();
                $lastResendKey = 'last_verification_resend_'.$emailresend;
                $lastResend = $session->get($lastResendKey);
                $now = new \DateTime();
                
                if ($lastResend) {
                    $minutesSinceLastResend = ($now->getTimestamp() - $lastResend) / 60;
                    if ($minutesSinceLastResend < 5) {
                        $this->addFlash(
                            'error',
                            sprintf(
                                'Molimo sačekajte još %d minuta pre nego što ponovo pošaljete link za potvrdu email adrese.',
                                ceil(5 - $minutesSinceLastResend)
                                )
                            );
                        $this->addFlash('unverified_user_no_alert', $emailresend);
                        return $this->redirectToRoute('login');
                    }
                }
                
                $userRepository->sendVerificationLink($user, null);
                $session->set($lastResendKey, $now->getTimestamp());
                
                $this->addFlash('success', 'Link za potvrdu email je ponovo poslat na vašu adresu. Molimo proverite vaš inbox.');
            }

            return $this->redirectToRoute('login');
        }

        $email = $request->query->get('email', $authenticationUtils->getLastUsername());

        return $this->render('security/login.html.twig', [
            'email' => $email,
        ]);
    }

    #[Route('/login_check', name: 'login_check')]
    public function check(): never
    {
        throw new \LogicException('This code should never be reached');
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
