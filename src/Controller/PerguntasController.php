<?php

namespace App\Controller;

use App\Enum\PerguntaEnum;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PerguntasController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/perguntas", name="app_perguntas_list", methods={"GET"})
     */
    public function list(): JsonResponse
    {
        return new JsonResponse([
            'paginas' => PerguntaEnum::getPaginas(),
            'perguntas' => array_values(PerguntaEnum::getPerguntas())
        ]);
    }

    /**
     * @Route("/perguntas/respostas", name="app_perguntas_respostas", methods={"GET"})
     */
    public function getRespostas(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Usuário não autenticado.'], Response::HTTP_UNAUTHORIZED);
        }

        $informacoes = $user->getInformacoes() ?? [];
        $respostasRecentes = [];

        foreach ($informacoes as $key => $historico) {
            if (is_array($historico) && !empty($historico)) {
                // Pega apenas a primeira (mais recente)
                $respostasRecentes[$key] = $historico[0];
            }
        }

        return new JsonResponse($respostasRecentes);
    }

    /**
     * @Route("/perguntas/responder", name="app_perguntas_responder", methods={"POST"})
     */
    public function responder(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['message' => 'Usuário não autenticado.'], Response::HTTP_UNAUTHORIZED);
            }

            $respostasRecebidas = json_decode($request->getContent(), true);

            if (!is_array($respostasRecebidas) || empty($respostasRecebidas)) {
                return new JsonResponse(['message' => 'Nenhuma resposta enviada ou formato inválido (esperado objeto chave => valor).'], Response::HTTP_BAD_REQUEST);
            }

            $informacoes = $user->getInformacoes() ?? [];
            $erros = [];
            $now = (new \DateTime())->format('Y-m-d H:i:s');

            foreach ($respostasRecebidas as $perguntaKey => $resposta) {
                $pergunta = PerguntaEnum::getPergunta($perguntaKey);
                
                if (!$pergunta) {
                    $erros[] = "Pergunta '$perguntaKey' não encontrada.";
                    continue;
                }

                // Validação de tamanho para texto
                if ($pergunta['tipo'] === 'texto' && isset($pergunta['max_length'])) {
                    if ($resposta !== null && mb_strlen((string)$resposta) > $pergunta['max_length']) {
                        $erros[] = sprintf("Resposta para '%s' excede o limite de %d caracteres.", $pergunta['texto'], $pergunta['max_length']);
                        continue;
                    }
                }

                // Validação para tipo select (se houver opções definidas)
                if ($pergunta['tipo'] === 'select' && isset($pergunta['opcoes'])) {
                    if ($resposta !== null && !in_array($resposta, $pergunta['opcoes'])) {
                        $erros[] = sprintf("Resposta '%s' não é uma opção válida para a pergunta '%s'.", $resposta, $pergunta['texto']);
                        continue;
                    }
                }

                // Estrutura de histórico: mantendo as respostas antigas
                if (!isset($informacoes[$perguntaKey]) || !is_array($informacoes[$perguntaKey])) {
                    $informacoes[$perguntaKey] = [];
                }

                // Verifica se a resposta nova é igual à última salva (topo do array)
                if (!empty($informacoes[$perguntaKey])) {
                    $ultimaResposta = $informacoes[$perguntaKey][0]['resposta'] ?? null;
                    if ($ultimaResposta === $resposta) {
                        continue; // Pula o registro se o valor não mudou
                    }
                }

                // Adiciona a nova resposta no topo (início do array)
                array_unshift($informacoes[$perguntaKey], [
                    'data' => $now,
                    'resposta' => $resposta
                ]);

                // Limita o histórico a 20 entradas para evitar que o JSON cresça demais
                if (count($informacoes[$perguntaKey]) > 20) {
                    $informacoes[$perguntaKey] = array_slice($informacoes[$perguntaKey], 0, 20);
                }
            }

            if (!empty($erros)) {
                return new JsonResponse([
                    'message' => 'Houve erros na validação de algumas respostas.',
                    'errors' => $erros
                ], Response::HTTP_BAD_REQUEST);
            }

            $user->setInformacoes($informacoes);

            $em = $this->doctrine->getManager();
            $em->persist($user);
            $em->flush();

            return new JsonResponse([
                'message' => 'Respostas registradas com sucesso.',
                'user' => $user
            ]);
        } catch (\Throwable $th) {
            return new JsonResponse(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
