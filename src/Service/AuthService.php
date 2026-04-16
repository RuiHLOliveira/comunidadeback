<?php

namespace App\Service;

use App\Entity\InvitationToken;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthService
{
    private $doctrine;
    private $encoder;
    private $senderEmail;
    private $cache;
    private $logger;
    private $googleScriptUrl;

    public function __construct(
        ManagerRegistry $doctrine,
        UserPasswordEncoderInterface $encoder,
        string $senderEmail,
        \Symfony\Contracts\Cache\CacheInterface $cache,
        LoggerInterface $logger,
        string $googleScriptUrl
    ) {
        $this->doctrine = $doctrine;
        $this->encoder = $encoder;
        $this->senderEmail = $senderEmail;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->googleScriptUrl = $googleScriptUrl;
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
        try {
            $startedAt = microtime(true);
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $cacheKey = 'login_code_' . str_replace(['.', '@'], '_', $email);
            $this->cache->get($cacheKey, function (\Symfony\Contracts\Cache\ItemInterface $item) use ($code) {
                $item->expiresAfter(300); // 5 minutos
                return $code;
            });

            $afterCacheAt = microtime(true);

            $payload = [
                'to' => $email,
                'subject' => 'Seu codigo de acesso',
                'body' => "Seu codigo de acesso e: $code. Ele expira em 5 minutos.",
                'from' => $this->senderEmail,
            ];

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/json\r\n",
                    'content' => json_encode($payload),
                    'timeout' => 30,
                    'ignore_errors' => true,
                ],
            ]);

            $beforeSendAt = microtime(true);
            $responseBody = @file_get_contents($this->googleScriptUrl, false, $context);
            $afterSendAt = microtime(true);

            $responseData = json_decode($responseBody, true);
            $statusCode = null;
            if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
                $statusCode = (int) $matches[1];
            }

            if ($responseBody === false || (!isset($responseData['status'])) || $responseData['status'] == 'error') {
                
                $this->logger->error('auth.request_code timing mail error', [
                    'email' => $email,
                    'cache_ms' => (int) (($afterCacheAt - $startedAt) * 1000),
                    'mailer_send_ms' => (int) (($afterSendAt - $beforeSendAt) * 1000),
                    'total_ms' => (int) (($afterSendAt - $startedAt) * 1000),
                    'transport' => 'google_script_http',
                    'google_script_status' => $statusCode,
                    'google_script_responsebody' => $responseBody
                ]);

                throw new \RuntimeException(sprintf(
                    'Failed to send login code via Google Script. Status: %s',
                    $statusCode ?? 'unknown'
                ));
            }

            return $code;

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function verifyCode(string $email, string $code): bool
    {
        $cacheKey = 'login_code_' . str_replace(['.', '@'], '_', $email);
        $savedCode = $this->cache->get($cacheKey, function (\Symfony\Contracts\Cache\ItemInterface $item) {
            $item->expiresAfter(0); // Garante que nao retorne nada se nao existir
            return null;
        });

        if ($savedCode && $savedCode === $code) {
            $this->cache->delete($cacheKey); // Uso unico
            return true;
        }

        return false;
    }

    public function registerUser(User $user, string $invitationTokenString)
    {
        try {
            $entityManager = $this->doctrine->getManager();
            $entityManager->getConnection()->beginTransaction();

            $invitationToken = $this->doctrine->getRepository(InvitationToken::class)->findOneBy([
                'invitation_token' => $invitationTokenString,
                'active' => true,
            ]);

            if ($invitationToken == null) {
                throw new NotFoundHttpException('Invitation Token not found or already used.');
            }

            $invitationTokenEmail = $invitationToken->getEmail();
            if ($invitationTokenEmail !== null && $invitationTokenEmail !== $user->getEmail()) {
                throw new NotFoundHttpException("This email can't use this Invitation Token.");
            }

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
