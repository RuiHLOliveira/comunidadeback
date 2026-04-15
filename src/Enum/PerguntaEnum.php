<?php

namespace App\Enum;

class PerguntaEnum
{
    public const PAGINA_1 = 1;
    public const PAGINA_2 = 2;
    public const PAGINA_3 = 3;
    public const PAGINA_4 = 4;

    public const NOME_COMPLETO = 'nome_completo';
    public const NOME_PREFERIDO = 'nome_preferido';
    public const LOCALIZACAO = 'localizacao';
    public const LINKEDIN = 'linkedin';
    public const GITHUB = 'github';
    public const STATUS_ATUAL = 'status_atual';
    public const EMPRESA_ATUAL = 'empresa_atual';
    public const CARGO_ATUAL = 'cargo_atual';
    public const TEMPO_EXPERIENCIA = 'tempo_experiencia';
    public const FAIXA_RENDA = 'faixa_renda';
    public const OBJETIVO_MENTORIA = 'objetivo_mentoria';
    public const PRAZO_OBJETIVO = 'prazo_objetivo';
    public const MOTIVO_MENTORIA = 'motivo_mentoria';
    public const TENTATIVAS_ANTERIORES = 'tentativas_anteriores';
    public const LINGUAGENS_FRAMEWORKS = 'linguagens_frameworks';
    public const FERRAMENTAS = 'ferramentas';
    public const BANCO_DADOS = 'banco_dados';
    public const GIT_TIME = 'git_time';
    public const DEPLOY_PRODUCAO = 'deploy_producao';
    public const COMO_APRENDE_MELHOR = 'como_aprende_melhor';
    public const HORAS_DEDICACAO = 'horas_dedicacao';
    public const DISPONIBILIDADE = 'disponibilidade';
    public const DIFICULDADES = 'dificuldades';
    public const COMO_APRENDEU_PROG = 'como_aprendeu_prog';
    public const CURSOS_CERTIFICACOES = 'cursos_certificacoes';
    public const GITHUB_PORTFOLIO = 'github_portfolio';

    public static function getPaginas(): array
    {
        return [
            ['id' => self::PAGINA_1, 'titulo' => 'Informações Pessoais'],
            ['id' => self::PAGINA_2, 'titulo' => 'Objetivos e Carreira'],
            ['id' => self::PAGINA_3, 'titulo' => 'Conhecimento Técnico'],
            ['id' => self::PAGINA_4, 'titulo' => 'Interpessoal e Dedicação'],
        ];
    }

    public static function getPerguntas(): array
    {
        return [
            // pagina 1 pessoal
            self::NOME_COMPLETO => [
                'key' => self::NOME_COMPLETO,
                'texto' => 'Nome completo',
                'tipo' => 'texto',
                'max_length' => 100,
                'pagina' => self::PAGINA_1,
            ],
            self::NOME_PREFERIDO => [
                'key' => self::NOME_PREFERIDO,
                'texto' => 'Nome preferido / Como gosta de ser chamado',
                'tipo' => 'texto',
                'max_length' => 30,
                'pagina' => self::PAGINA_1,
            ],
            self::LOCALIZACAO => [
                'key' => self::LOCALIZACAO,
                'texto' => 'Localização (cidade/país e fuso horário)',
                'tipo' => 'texto',
                'max_length' => 100,
                'pagina' => self::PAGINA_1,
            ],
            self::LINKEDIN => [
                'key' => self::LINKEDIN,
                'texto' => 'Seu LinkedIn',
                'tipo' => 'texto',
                'max_length' => 100,
                'pagina' => self::PAGINA_1,
            ],
            self::GITHUB => [
                'key' => self::GITHUB,
                'texto' => 'Seu Github',
                'tipo' => 'texto',
                'max_length' => 100,
                'pagina' => self::PAGINA_1,
            ],
            // pagina 2 objetivos
            self::STATUS_ATUAL => [
                'key' => self::STATUS_ATUAL,
                'texto' => 'Status atual',
                'tipo' => 'select',
                'opcoes' => ['Estudante', 'Estagiário', 'Júnior empregado', 'Júnior desempregado', 'Migrando de carreira', 'Pleno/Sênior buscando evolução'],
                'pagina' => self::PAGINA_2,
            ],
            self::EMPRESA_ATUAL => [
                'key' => self::EMPRESA_ATUAL,
                'texto' => 'Empresa atual (se empregado)',
                'tipo' => 'texto',
                'max_length' => 100,
                'pagina' => self::PAGINA_2,
            ],
            self::CARGO_ATUAL => [
                'key' => self::CARGO_ATUAL,
                'texto' => 'Cargo atual',
                'tipo' => 'texto',
                'max_length' => 100,
                'pagina' => self::PAGINA_2,
            ],
            self::TEMPO_EXPERIENCIA => [
                'key' => self::TEMPO_EXPERIENCIA,
                'texto' => 'Tempo de experiência na área',
                'tipo' => 'texto',
                'max_length' => 50,
                'pagina' => self::PAGINA_2,
            ],
            self::FAIXA_RENDA => [
                'key' => self::FAIXA_RENDA,
                'texto' => 'Faixa de renda atual e objetivo',
                'tipo' => 'select',
                'opcoes' => ['Sem renda', 'Até R$2k', 'R$2k-4k', 'R$4k-7k', 'R$7k-12k', 'Acima de R$12k'],
                'pagina' => self::PAGINA_2,
            ],
            self::OBJETIVO_MENTORIA => [
                'key' => self::OBJETIVO_MENTORIA,
                'texto' => 'Objetivo principal com a mentoria (até 2) (Conseguir primeiro emprego, Trocar de empresa, Aumentar salário, Virar tech lead, Migrar para outra stack, Trabalhar no exterior/remoto, Montar produto próprio, Melhorar qualidade de código, Evoluir soft skills técnicas)',
                'tipo' => 'texto',
                'max_length' => 255,
                'pagina' => self::PAGINA_2,
            ],
            self::PRAZO_OBJETIVO => [
                'key' => self::PRAZO_OBJETIVO,
                'texto' => 'Prazo desejado para atingir o objetivo',
                'tipo' => 'select',
                'opcoes' => ['3 meses', '6 meses', '1 ano', 'Sem prazo definido'],
                'pagina' => self::PAGINA_2,
            ],
            self::MOTIVO_MENTORIA => [
                'key' => self::MOTIVO_MENTORIA,
                'texto' => 'Por que quer uma mentoria agora?',
                'tipo' => 'texto',
                'max_length' => 500,
                'pagina' => self::PAGINA_2,
            ],
            self::TENTATIVAS_ANTERIORES => [
                'key' => self::TENTATIVAS_ANTERIORES,
                'texto' => 'O que já tentou antes para atingir esse objetivo?',
                'tipo' => 'texto',
                'max_length' => 500,
                'pagina' => self::PAGINA_2,
            ],
            // pagina 3 parte técnica
            self::LINGUAGENS_FRAMEWORKS => [
                'key' => self::LINGUAGENS_FRAMEWORKS,
                'texto' => 'Linguagens e frameworks que conhece',
                'tipo' => 'texto',
                'max_length' => 500,
                'pagina' => self::PAGINA_3,
            ],
            self::FERRAMENTAS => [
                'key' => self::FERRAMENTAS,
                'texto' => 'Ferramentas (multi-select com nível: Docker, Git, AWS, etc.)',
                'tipo' => 'texto',
                'max_length' => 500,
                'pagina' => self::PAGINA_3,
            ],
            self::BANCO_DADOS => [
                'key' => self::BANCO_DADOS,
                'texto' => 'Banco de dados (Relacionais e não-relacionais, modelagem, queries, performance)',
                'tipo' => 'texto',
                'max_length' => 500,
                'pagina' => self::PAGINA_3,
            ],
            self::GIT_TIME => [
                'key' => self::GIT_TIME,
                'texto' => 'Já trabalhou com Git em time?',
                'tipo' => 'texto',
                'max_length' => 255,
                'pagina' => self::PAGINA_3,
            ],
            self::DEPLOY_PRODUCAO => [
                'key' => self::DEPLOY_PRODUCAO,
                'texto' => 'Já fez deploy de algo em produção?',
                'tipo' => 'texto',
                'max_length' => 255,
                'pagina' => self::PAGINA_3,
            ],
            self::CURSOS_CERTIFICACOES => [
                'key' => self::CURSOS_CERTIFICACOES,
                'texto' => 'Cursos ou certificações relevantes já feitos',
                'tipo' => 'texto',
                'max_length' => 500,
                'pagina' => self::PAGINA_3,
            ],
            self::GITHUB_PORTFOLIO => [
                'key' => self::GITHUB_PORTFOLIO,
                'texto' => 'Tem projetos no GitHub ou portfólio? (link)',
                'tipo' => 'texto',
                'max_length' => 255,
                'pagina' => self::PAGINA_3,
            ],
            // pagina 4 interpessoal
            self::COMO_APRENDE_MELHOR => [
                'key' => self::COMO_APRENDE_MELHOR,
                'texto' => 'Como aprende melhor',
                'tipo' => 'select',
                'opcoes' => ['Vendo exemplos práticos', 'Fazendo exercícios', 'Lendo documentação', 'Projetos reais', 'Explicando para outros', 'Vídeos/aulas'],
                'pagina' => self::PAGINA_4,
            ],
            self::HORAS_DEDICACAO => [
                'key' => self::HORAS_DEDICACAO,
                'texto' => 'Quantas horas por semana pode dedicar à mentoria?',
                'tipo' => 'select',
                'opcoes' => ['Até 2h', '2h-5h', '5h-10h', 'Mais de 10h'],
                'pagina' => self::PAGINA_4,
            ],
            self::DISPONIBILIDADE => [
                'key' => self::DISPONIBILIDADE,
                'texto' => 'Qual o melhor dia e horário para sessões? (disponibilidade semanal — grid de horários)',
                'tipo' => 'texto',
                'max_length' => 500,
                'pagina' => self::PAGINA_4,
            ],
            self::DIFICULDADES => [
                'key' => self::DIFICULDADES,
                'texto' => 'Tem dificuldade com alguma dessas situações? (Terminar o que começo, Pedir ajuda, Aceitar feedback, Autoestima técnica baixa, Procrastinação, Foco e consistência)',
                'tipo' => 'texto',
                'max_length' => 500,
                'pagina' => self::PAGINA_4,
            ],
            self::COMO_APRENDEU_PROG => [
                'key' => self::COMO_APRENDEU_PROG,
                'texto' => 'Como aprendeu programação? (Graduação, Bootcamp, Autodidata (cursos online), Autodidata (projects), No trabalho, Faculdade técnica)',
                'tipo' => 'texto',
                'max_length' => 500,
                'pagina' => self::PAGINA_4,
            ],
        ];
    }

    public static function getPergunta(string $key): ?array
    {
        return self::getPerguntas()[$key] ?? null;
    }
}
