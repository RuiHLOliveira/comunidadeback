<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\InvitationToken;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthService
{
    
    private $doctrine;
    private $encoder;
    private $mailer;
    private $senderEmail;
    private $cache;

    public function __construct(ManagerRegistry $doctrine, UserPasswordEncoderInterface $encoder, \Symfony\Component\Mailer\MailerInterface $mailer, string $senderEmail, \Symfony\Contracts\Cache\CacheInterface $cache)
    {
        $this->doctrine = $doctrine;
        $this->encoder = $encoder;
        $this->mailer = $mailer;
        $this->senderEmail = $senderEmail;
        $this->cache = $cache;
    }

    public function canRequestCode(string $ip): bool
    {
        $cacheKey = 'login_attempt_' . str_replace(['.', ':'], '_', $ip);
        $attempts = $this->cache->get($cacheKey, function (\Symfony\Contracts\Cache\ItemInterface $item) {
            $item->expiresAfter(3600); // 1 hora
            return 0;
        });

        return $attempts < 3;
    }

    public function logAttempt(string $ip): void
    {
        $cacheKey = 'login_attempt_' . str_replace(['.', ':'], '_', $ip);
        $attempts = $this->cache->get($cacheKey, function (\Symfony\Contracts\Cache\ItemInterface $item) {
            $item->expiresAfter(3600);
            return 0;
        });
        
        $this->cache->delete($cacheKey);
        $this->cache->get($cacheKey, function (\Symfony\Contracts\Cache\ItemInterface $item) use ($attempts) {
            $item->expiresAfter(3600);
            return $attempts + 1;
        });
    }

    public function generateLoginCode(string $email)
    {
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $cacheKey = 'login_code_' . str_replace(['.','@'], '_', $email);
        $this->cache->get($cacheKey, function (\Symfony\Contracts\Cache\ItemInterface $item) use ($code) {
            $item->expiresAfter(300); // 5 minutos
            return $code;
        });

        $emailObj = (new \Symfony\Component\Mime\Email())
            ->from($this->senderEmail)
            ->to($email)
            ->subject('Seu código de acesso')
            ->text("Seu código de acesso é: $code. Ele expira em 5 minutos.");
        
        $this->mailer->send($emailObj);
        
        return $code;
    }

    public function verifyCode(string $email, string $code): bool
    {
        $cacheKey = 'login_code_' . str_replace(['.','@'], '_', $email);
        $savedCode = $this->cache->get($cacheKey, function (\Symfony\Contracts\Cache\ItemInterface $item) {
            $item->expiresAfter(0); // Garante que não retorne nada se não existir
            return null;
        });

        if ($savedCode && $savedCode === $code) {
            $this->cache->delete($cacheKey); // Uso único
            return true;
        }

        return false;
    }

    public function registerUser(User $user, string $invitationTokenString) {
        
        try {
            $entityManager = $this->doctrine->getManager();
            $entityManager->getConnection()->beginTransaction();

            $invitationToken = $this->doctrine->getRepository(InvitationToken::class)->findOneBy([
                'invitation_token' => $invitationTokenString,
                'active' => true
            ]);
            
            if($invitationToken == null) throw new NotFoundHttpException("Invitation Token not found or already used.");
                
            $invitationTokenEmail = $invitationToken->getEmail();
            if($invitationTokenEmail !== null && $invitationTokenEmail !== $user->getEmail()) throw new NotFoundHttpException("This email can't use this Invitation Token.");

            $user->setPassword($this->encoder->encodePassword($user, $user->getPassword()));

            $entityManager->persist($user);

            $invitationToken->setActive(false);
            $entityManager->persist($invitationToken);
            
            $entityManager->flush();

            $entityManager->getConnection()->commit();

            return $user;

        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

    public function findByEmail($email)
    {
        return $this->doctrine->getRepository(User::class)->findOneBy(['email' => $email]);
    }

}