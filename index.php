<?php
require_once __DIR__ . '/conexao.php';

// Consulta os dados do Banner no banco de dados
$stmt_hero = $pdo->query("SELECT * FROM banner_principal WHERE id = 1");
$hero = $stmt_hero->fetch(PDO::FETCH_ASSOC);

// Fallback: Valores padrão caso a tabela ainda não tenha registro id=1
if (!$hero) {
    $hero = [
        'badge' => 'POLO OFICIAL FACLA',
        'titulo' => 'FENATEHD EDUCACIONAL',
        'subtitulo' => 'FACULDADES LA',
        'descricao' => 'Transformando vidas através da educação de qualidade. Seu futuro começa aqui.'
    ];
}

$msg_lead = '';

// Processamento do Formulário de Leads
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_lead'])) {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $curso_interesse = trim($_POST['curso_interesse'] ?? '');

    if (!empty($nome) && !empty($email) && !empty($telefone)) {
        $stmt = $pdo->prepare("INSERT INTO leads (nome, email, telefone, curso_interesse) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $email, $telefone, $curso_interesse]);
        $msg_lead = "sucesso";
    } else {
        $msg_lead = "erro";
    }
}

// Busca todos os cursos para a vitrine e para o select de Leads
$stmt = $pdo->query("SELECT * FROM cursos ORDER BY id DESC");
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca as informações do Sobre o Polo
$stmt_polo = $pdo->query("SELECT * FROM sobre_polo WHERE id = 1");
$polo = $stmt_polo->fetch(PDO::FETCH_ASSOC);

// Busca as Vantagens do Banco de Dados
$stmt_v = $pdo->query("SELECT * FROM vantagens ORDER BY id ASC");
$vantagens = $stmt_v->fetchAll(PDO::FETCH_ASSOC);

// Busca todos os diferenciais cadastrados na tabela "diferenciais" (ou fallback para vantagens)
try {
    $stmt_diferenciais = $pdo->query("SELECT * FROM diferenciais ORDER BY id ASC");
    $lista_diferenciais = $stmt_diferenciais->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $lista_diferenciais = [];
}

// Busca os Professores/Corpo Docente
try {
    $stmt_professores = $pdo->query("SELECT * FROM professores ORDER BY id ASC");
    $professores = $stmt_professores->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $professores = [];
}

// Consulta os professores cadastrados
$stmt_prof_site = $pdo->query("SELECT * FROM professores ORDER BY id ASC");
$professores_site = $stmt_prof_site->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nossos Cursos - Fenatehd Educacional</title>

    <!-- Fonte Moderna Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        /* ==========================================================================
           1. VARIÁVEIS DE TEMA (LIGHT & DARK)
           ========================================================================== */
        :root {
            --bg-principal: #f8f9fa;
            --bg-card: #ffffff;
            --bg-destaque: #ffffff;
            --bg-input: #f8f9fa;
            --texto-principal: #212529;
            --texto-titulos: #111111;
            --texto-secundario: #555555;
            --borda-cor: #e0e0e0;
            --sombra-card: rgba(0, 0, 0, 0.05);
            --icon-wrapper-bg: #fff3e0;
            --icon-wrapper-border: #ffe0b2;
            --laranja-padrao: #f57c00;
        }

        body.dark-mode {
            --bg-principal: #000000;
            --bg-card: #111111;
            --bg-destaque: #090909;
            --bg-input: #141414;
            --texto-principal: #ffffff;
            --texto-titulos: #ffffff;
            --texto-secundario: #cccccc;
            --borda-cor: #222222;
            --sombra-card: rgba(0, 0, 0, 0.6);
            --icon-wrapper-bg: #1a0e05;
            --icon-wrapper-border: #f57c00;
        }

        /* ==========================================================================
           2. RESET & ESTILOS GERAIS
           ========================================================================== */
        * { 
            box-sizing: border-box; 
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body { 
            background-color: var(--bg-principal); 
            color: var(--texto-principal); 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, Arial, sans-serif; 
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .linha-destaque {
            width: 60px;
            height: 4px;
            background-color: var(--laranja-padrao);
            border-radius: 2px;
            margin-bottom: 25px;
        }

        /* ==========================================================================
           3. BOTÃO FLUTUANTE TROCA DE TEMA (LIGHT / DARK)
           ========================================================================== */
        .theme-toggle-btn {
            position: fixed;
            bottom: 25px;
            left: 25px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--laranja-padrao);
            color: #ffffff;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        .theme-toggle-btn:hover {
            transform: scale(1.1);
        }

        .theme-toggle-btn svg {
            width: 24px;
            height: 24px;
            fill: #ffffff;
        }

        /* ==========================================================================
           4. SEÇÃO HERO / BANNER PRINCIPAL
           ========================================================================== */
        .hero-section {
            background-color: var(--bg-card);
            padding: 90px 20px 70px 20px;
            text-align: center;
            color: var(--texto-principal);
            position: relative;
            border-bottom: 1px solid var(--borda-cor);
            transition: background-color 0.3s ease;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            background-color: #ff5500;
            color: #ffffff;
            font-size: 0.8rem;
            font-weight: 800;
            padding: 6px 16px;
            border-radius: 50px;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(255, 85, 0, 0.25);
        }

        .hero-titulo {
            font-size: 3.2rem;
            font-weight: 900;
            color: var(--texto-titulos);
            letter-spacing: 1.5px;
            margin-bottom: 12px;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .hero-subtitulo {
            font-size: 1.35rem;
            font-weight: 800;
            color: #ff5500;
            letter-spacing: 1.5px;
            margin-bottom: 25px;
            text-transform: uppercase;
        }

        .hero-descricao {
            font-size: 1.1rem;
            color: var(--texto-secundario);
            max-width: 680px;
            margin: 0 auto;
            line-height: 1.6;
            font-weight: 400;
        }

        /* ==========================================================================
           5. SEÇÃO VANTAGENS / CARDS LARANJAS
           ========================================================================== */
        .vantagens-section {
            padding: 60px 20px;
            background-color: var(--bg-principal);
            transition: background-color 0.3s ease;
        }

        .vantagens-container {
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(35px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-vantagem {
            background-color: #f56506;
            border-radius: 20px;
            padding: 35px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
            box-shadow: 0 10px 25px rgba(245, 101, 6, 0.18);
            transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), 
                        box-shadow 0.4s cubic-bezier(0.165, 0.84, 0.44, 1),
                        background-color 0.3s ease;
            animation: fadeInUp 0.8s cubic-bezier(0.165, 0.84, 0.44, 1) both;
            will-change: transform, opacity;
        }

        .card-vantagem:nth-child(1) { animation-delay: 0.1s; }
        .card-vantagem:nth-child(2) { animation-delay: 0.25s; }
        .card-vantagem:nth-child(3) { animation-delay: 0.4s; }

        .card-vantagem:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 35px rgba(245, 101, 6, 0.3);
            background-color: #ff6a0a;
        }

        .card-vantagem .conteudo-texto {
            flex: 1;
            color: #ffffff;
        }

        .card-vantagem .conteudo-texto h2 {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 14px;
            line-height: 1.25;
            letter-spacing: -0.5px;
        }

        .card-vantagem .conteudo-texto p {
            font-size: 1rem;
            line-height: 1.6;
            opacity: 0.95;
            margin: 0;
        }

        .card-vantagem .conteudo-imagem {
            flex: 1;
            max-width: 440px;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .card-vantagem .conteudo-imagem img {
            width: 100%;
            height: 230px;
            object-fit: cover;
            display: block;
            transition: transform 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .card-vantagem:hover .conteudo-imagem img {
            transform: scale(1.05);
        }

        /* ==========================================================================
           6. VITRINE DE CURSOS
           ========================================================================== */
        .cursos-section {
            padding: 80px 20px;
            background-color: var(--bg-card);
            transition: background-color 0.3s ease;
        }

        .section-title { 
            text-align: center; 
            font-size: 2.3em; 
            margin-bottom: 40px; 
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: var(--texto-titulos);
        }

        .cursos-grid { 
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px; 
            max-width: 1200px;
            margin: 0 auto;
        }

        .curso-link {
            text-decoration: none;
            color: inherit;
            display: flex;
        }

        .curso-card { 
            background: var(--bg-card); 
            color: var(--texto-principal); 
            border-radius: 16px; 
            width: 100%;
            overflow: hidden; 
            border: 1px solid var(--borda-cor);
            box-shadow: 0 10px 20px var(--sombra-card);
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .curso-card:hover {
            transform: translateY(-8px);
            border-color: var(--laranja-padrao);
            box-shadow: 0 15px 30px rgba(245, 124, 0, 0.15);
        }

        .curso-card-img-wrapper {
            width: 100%;
            height: 190px;
            overflow: hidden;
            background-color: #111;
        }

        .curso-card img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            transition: transform 0.4s ease;
        }

        .curso-card:hover img {
            transform: scale(1.05);
        }

        .curso-info { 
            padding: 24px; 
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .curso-info h3 { 
            margin: 0 0 15px 0; 
            font-size: 1.15em; 
            font-weight: 700;
            color: var(--texto-titulos); 
            line-height: 1.35;
            min-height: 2.7em;
        }

        .curso-preco { 
            font-weight: 600; 
            color: var(--texto-secundario); 
            font-size: 0.9em; 
            margin-top: auto; 
            padding-top: 15px;
            border-top: 1px solid var(--borda-cor);
            display: flex;
            align-items: baseline;
            gap: 5px;
        }

        .curso-preco span {
            color: var(--texto-titulos);
            font-size: 1.3em;
            font-weight: 800;
        }

        /* ==========================================================================
           7. SEÇÃO PROFESSORES / CORPO DOCENTE
           ========================================================================== */
        .professores-section {
            padding: 80px 20px;
            background-color: var(--bg-principal);
            border-top: 1px solid var(--borda-cor);
            transition: background-color 0.3s ease;
        }

        .professores-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .card-professor {
            background-color: var(--bg-card);
            border: 1px solid var(--borda-cor);
            border-radius: 20px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 5px 20px var(--sombra-card);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .card-professor:hover {
            transform: translateY(-8px);
            border-color: var(--laranja-padrao);
            box-shadow: 0 15px 30px rgba(245, 124, 0, 0.12);
        }

        .foto-professor-wrapper {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            overflow: hidden;
            margin-bottom: 20px;
            border: 4px solid var(--icon-wrapper-bg);
            box-shadow: 0 4px 15px rgba(245, 124, 0, 0.2);
            background-color: var(--borda-cor);
        }

        .foto-professor-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .card-professor:hover .foto-professor-wrapper img {
            transform: scale(1.08);
        }

        .card-professor h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--texto-titulos);
            margin-bottom: 6px;
        }

        .badge-especialidade {
            display: inline-block;
            background-color: var(--icon-wrapper-bg);
            color: var(--laranja-padrao);
            font-weight: 700;
            font-size: 0.8rem;
            padding: 4px 12px;
            border-radius: 50px;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .card-professor p {
            font-size: 0.9rem;
            color: var(--texto-secundario);
            line-height: 1.5;
            margin: 0;
        }

        /* ==========================================================================
           8. SOBRE O POLO
           ========================================================================== */
        .sobre-polo-section {
            background-color: var(--bg-card);
            color: var(--texto-principal);
            padding: 80px 20px;
            border-top: 1px solid var(--borda-cor);
            border-bottom: 1px solid var(--borda-cor);
            transition: background-color 0.3s ease;
        }

        .sobre-polo-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 50px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .sobre-polo-text { 
            flex: 1; 
        }

        .sobre-polo-text h2 {
            font-size: 2.2em;
            font-weight: 800;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--texto-titulos);
        }

        .sobre-polo-text p {
            color: var(--texto-secundario);
            font-size: 1.02em;
            line-height: 1.7;
            margin-bottom: 18px;
        }

        .sobre-polo-media {
            flex: 1;
            display: flex;
            justify-content: center;
        }

        .polo-icon-card {
            width: 100%;
            max-width: 450px;
            height: 330px;
            background: var(--bg-card);
            border: 1px solid var(--borda-cor);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px var(--sombra-card);
        }

        .polo-img {
            width: 100%;
            max-width: 450px;
            height: 330px;
            object-fit: cover;
            border-radius: 20px;
            border: 1px solid var(--borda-cor);
            box-shadow: 0 10px 25px var(--sombra-card);
        }

        /* ==========================================================================
           9. FORMULÁRIO DE LEADS
           ========================================================================== */
        .leads-section {
            background-color: var(--bg-card);
            padding: 70px 20px 90px 20px;
            text-align: center;
            color: var(--texto-titulos);
            transition: background-color 0.3s ease;
        }

        .leads-container {
            max-width: 680px;
            margin: 0 auto;
            background-color: var(--bg-destaque);
            border: 1px solid var(--borda-cor);
            padding: 45px 35px;
            border-radius: 24px;
            box-shadow: 0 15px 35px var(--sombra-card);
        }

        .leads-section h2 {
            font-size: 2em;
            font-weight: 800;
            margin-bottom: 10px;
            color: var(--texto-titulos);
            letter-spacing: -0.5px;
        }

        .leads-section h2 span { 
            color: var(--laranja-padrao); 
        }

        .leads-section p {
            color: var(--texto-secundario);
            margin-bottom: 30px;
            font-size: 1.05em;
        }

        .leads-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .form-group-row {
            display: flex;
            gap: 16px;
        }

        .form-group-row input { 
            flex: 1; 
        }

        .leads-form input, 
        .leads-form select {
            width: 100%;
            padding: 15px 18px;
            border-radius: 10px;
            border: 1px solid var(--borda-cor);
            background-color: var(--bg-input);
            color: var(--texto-principal);
            font-size: 0.95em;
            font-family: inherit;
            outline: none;
            transition: all 0.25s ease;
        }

        .leads-form input:focus, 
        .leads-form select:focus {
            border-color: var(--laranja-padrao);
            box-shadow: 0 0 0 3px rgba(245, 124, 0, 0.15);
        }

        .btn-lead {
            background: linear-gradient(90deg, #f57c00 0%, #e65100 100%);
            color: #ffffff;
            border: none;
            padding: 16px;
            border-radius: 10px;
            font-size: 1.05em;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(245, 124, 0, 0.25);
        }

        .btn-lead:hover {
            opacity: 0.95;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(245, 124, 0, 0.35);
        }

        .alert-success {
            background: rgba(27, 94, 32, 0.2);
            border: 1px solid #2e7d32;
            color: #a5d6a7;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95em;
        }

        .alert-error {
            background: rgba(183, 28, 28, 0.2);
            border: 1px solid #c62828;
            color: #ef9a9a;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95em;
        }

        /* ==========================================================================
           10. SEÇÃO NOSSOS DIFERENCIAIS
           ========================================================================== */
        .diferenciais-section {
            background-color: var(--bg-principal);
            padding: 80px 20px;
            color: var(--texto-principal);
            text-align: center;
            border-top: 1px solid var(--borda-cor);
            transition: background-color 0.3s ease;
        }

        .titulo-secao {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: 1px;
            margin-bottom: 10px;
            text-transform: uppercase;
            color: var(--texto-titulos);
        }

        .subtitulo-secao {
            color: var(--texto-secundario);
            font-size: 1rem;
            margin-bottom: 45px;
        }

        .grid-diferenciais {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .card-diferencial {
            background-color: var(--bg-card);
            border: 1px solid var(--borda-cor);
            border-radius: 16px;
            padding: 32px 24px;
            text-align: left;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px var(--sombra-card);
        }

        .card-diferencial:hover {
            border-color: var(--laranja-padrao);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(245, 124, 0, 0.12);
        }

        .icon-wrapper {
            background-color: var(--icon-wrapper-bg);
            border: 1px solid var(--icon-wrapper-border);
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 22px;
        }

        .icon-wrapper img {
            max-width: 26px;
            max-height: 26px;
            object-fit: contain;
        }

        .icon-fallback {
            font-size: 1.4rem;
        }

        .card-diferencial h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--texto-titulos);
            margin-bottom: 10px;
        }

        .card-diferencial p {
            font-size: 0.92rem;
            color: var(--texto-secundario);
            line-height: 1.6;
            margin: 0;
        }

        .secao-professores {
    text-align: center;
    padding: 50px 20px;
    background-color: #fafafa;
}

.secao-professores .titulo-secao {
    font-size: 2em;
    font-weight: 900;
    letter-spacing: 1px;
    margin-bottom: 10px;
}

.secao-professores .linha-laranja {
    width: 60px;
    height: 4px;
    background-color: #ff6600;
    margin: 0 auto 40px auto;
    border-radius: 2px;
}

.container-professores {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
}

.card-professor {
    background: #fff;
    border: 2px solid #ff6600;
    border-radius: 20px;
    padding: 30px 20px;
    width: 300px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    display: flex;
    flex-direction: column;
    align-items: center;
}

.card-professor .wrapper-foto {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    overflow: hidden;
    margin-bottom: 20px;
    box-shadow: 0 0 15px rgba(255, 102, 0, 0.2);
    background-color: #e0e0e0;
}

.card-professor .wrapper-foto img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.card-professor h3 {
    font-size: 1.2em;
    font-weight: bold;
    color: #111;
    margin: 10px 0 8px 0;
}

.card-professor .badge-titulacao {
    background-color: #fff3e0;
    color: #ff6600;
    font-size: 0.75em;
    font-weight: 800;
    padding: 6px 14px;
    border-radius: 12px;
    text-transform: uppercase;
    margin-bottom: 15px;
}

.card-professor p {
    color: #666;
    font-size: 0.9em;
    line-height: 1.4;
    margin: 0;
}

        /* ==========================================================================
           11. RESPONSIVIDADE APERFEIÇOADA
           ========================================================================== */
        @media (max-width: 850px) {
            .sobre-polo-container { 
                flex-direction: column; 
            }
            .sobre-polo-text .linha-destaque {
                margin: 0 auto 25px auto;
            }
            .sobre-polo-text {
                text-align: center;
            }
            .polo-icon-card, .polo-img { 
                height: 260px; 
            }
            .form-group-row { 
                flex-direction: column; 
            }
            .leads-container {
                padding: 30px 20px;
            }
        }

        @media (max-width: 768px) {
            .hero-titulo {
                font-size: 2.2rem;
            }
            .hero-subtitulo {
                font-size: 1.1rem;
            }
            .hero-descricao {
                font-size: 0.98rem;
            }
            .card-vantagem {
                flex-direction: column;
                padding: 28px;
            }
            .card-vantagem .conteudo-imagem {
                max-width: 100%;
                width: 100%;
            }
            .card-vantagem .conteudo-imagem img {
                height: 200px;
            }
            .theme-toggle-btn {
                bottom: 15px;
                left: 15px;
                width: 44px;
                height: 44px;
            }
        }
    </style>
</head>
<body>

    <!-- BOTÃO DE ALTERNÂNCIA DE TEMA -->
    <button class="theme-toggle-btn" id="themeToggleBtn" title="Alternar Tema Claro/Escuro" aria-label="Alternar Tema">
        <svg id="moonIcon" viewBox="0 0 24 24">
            <path d="M12.3 2a10 10 0 0 0-1.9 20 10 10 0 0 0 10.1-8.5 1 1 0 0 0-1.1-1.1 8 8 0 1 1-8.2-9.3 1 1 0 0 0-1.1-1.1z"/>
        </svg>
        <svg id="sunIcon" viewBox="0 0 24 24" style="display: none;">
            <path d="M12 7a5 5 0 1 0 5 5 5 5 0 0 0-5-5zm0-5a1 1 0 0 0 1-1V1a1 1 0 0 0-2 0v0a1 1 0 0 0 1 1zm0 19a1 1 0 0 0-1 1v0a1 1 0 0 0 2 0v0a1 1 0 0 0-1-1zm10-10h0a1 1 0 0 0-1-1h0a1 1 0 0 0 0 2h0a1 1 0 0 0 1-1zM3 12a1 1 0 0 0-1-1H1a1 1 0 0 0 0 2h1a1 1 0 0 0 1-1zm16.07-7.07a1 1 0 0 0-1.41 0l-.71.71a1 1 0 1 0 1.41 1.41l.71-.71a1 1 0 0 0 0-1.41zM6.34 17.66a1 1 0 0 0-1.41 0l-.71.71a1 1 0 0 0 1.41 1.41l.71-.71a1 1 0 0 0 0-1.41zm12.73 12.73a1 1 0 0 0 0-1.41l-.71-.71a1 1 0 1 0-1.41 1.41l.71.71a1 1 0 0 0 1.41 0zM6.34 6.34a1 1 0 0 0 0-1.41l-.71-.71a1 1 0 0 0-1.41 1.41l.71.71a1 1 0 0 0 1.41 0z"/>
        </svg>
    </button>

    <!-- CABEÇALHO DINÂMICO -->
    <?php include 'header.php'; ?>

    <!-- HERO / BANNER PRINCIPAL -->
    <section class="hero-section">
        <div class="container">
            <?php if (!empty($hero['badge'])): ?>
                <div class="hero-badge"><?= htmlspecialchars($hero['badge']) ?></div>
            <?php endif; ?>

            <h1 class="hero-titulo"><?= htmlspecialchars($hero['titulo']) ?></h1>
            <h2 class="hero-subtitulo"><?= htmlspecialchars($hero['subtitulo']) ?></h2>
            <p class="hero-descricao"><?= htmlspecialchars($hero['descricao']) ?></p>
        </div>
    </section>

    <!-- SEÇÃO VANTAGENS -->
    <section class="vantagens-section">
        <div class="vantagens-container">
            <?php if (!empty($vantagens)): ?>
                <?php foreach ($vantagens as $v): ?>
                    <div class="card-vantagem">
                        <div class="conteudo-texto">
                            <h2><?= htmlspecialchars($v['titulo']) ?></h2>
                            <p><?= htmlspecialchars($v['descricao']) ?></p>
                        </div>
                        <div class="conteudo-imagem">
                            <img src="uploads/<?= htmlspecialchars($v['imagem']) ?>" alt="<?= htmlspecialchars($v['titulo']) ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card-vantagem">
                    <div class="conteudo-texto">
                        <h2>Por que escolher Nossos Cursos EAD?</h2>
                        <p>Nossos cursos a distância oferecem uma forma flexível e ao alcance de adquirir novas habilidades e avançar na sua carreira, tudo isso de acordo com seu próprio cronograma e de qualquer lugar do mundo.</p>
                    </div>
                    <div class="conteudo-imagem">
                        <img src="uploads/imagem1.jpg" alt="Por que escolher nossos cursos EAD">
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- VITRINE DE CURSOS -->
    <section class="cursos-section" id="cursos">
        <h2 class="section-title">Nossos Cursos</h2>

        <div class="cursos-grid">
            <?php if (!empty($cursos)): ?>
                <?php foreach ($cursos as $curso): ?>
                    <a href="curso.php?id=<?= $curso['id'] ?>" class="curso-link">
                        <div class="curso-card">
                            <div class="curso-card-img-wrapper">
                                <img src="uploads/<?= htmlspecialchars($curso['imagem']) ?>" alt="<?= htmlspecialchars($curso['titulo']) ?>">
                            </div>
                            <div class="curso-info">
                                <h3><?= htmlspecialchars($curso['titulo']) ?></h3>
                                <div class="curso-preco">
                                    A partir de R$ <span><?= number_format($curso['preco'], 2, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; color: #888888; grid-column: 1/-1;">Nenhum curso cadastrado ainda.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- SEÇÃO NOSSOS PROFESSORES -->
  <section class="secao-professores">
    <h2 class="titulo-secao">NOSSOS PROFESSORES</h2>
    <div class="linha-laranja"></div>

    <div class="container-professores">
        <?php if (!empty($professores_site)): ?>
            <?php foreach ($professores_site as $prof): ?>
                <div class="card-professor">
                    <div class="wrapper-foto">
                        <img src="uploads/<?= htmlspecialchars($prof['foto']) ?>" alt="<?= htmlspecialchars($prof['nome']) ?>">
                    </div>
                    <h3><?= htmlspecialchars($prof['nome']) ?></h3>
                    <span class="badge-titulacao"><?= htmlspecialchars($prof['titulacao']) ?></span>
                    <p><?= htmlspecialchars($prof['biografia']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

    <!-- SOBRE O POLO -->
    <?php if (!empty($polo)): ?>
    <section class="sobre-polo-section">
        <div class="sobre-polo-container">
            <div class="sobre-polo-text">
                <h2><?= htmlspecialchars($polo['titulo'] ?? 'Sobre o Polo') ?></h2>
                <div class="linha-destaque"></div>
                <?php
                    $paragrafos_polo = array_filter([
                        $polo['paragrafo_1'] ?? '',
                        $polo['paragrafo_2'] ?? '',
                        $polo['paragrafo_3'] ?? ''
                    ], static fn($paragrafo) => trim($paragrafo) !== '');

                    if (empty($paragrafos_polo) && !empty($polo['descricao'])) {
                        $paragrafos_polo = [$polo['descricao']];
                    }
                ?>
                <?php foreach ($paragrafos_polo as $paragrafo): ?>
                    <p><?= nl2br(htmlspecialchars($paragrafo)) ?></p>
                <?php endforeach; ?>
            </div>
            <div class="sobre-polo-media">
                <?php if (!empty($polo['imagem'])): ?>
                    <img src="uploads/<?= htmlspecialchars($polo['imagem']) ?>" class="polo-img" alt="Sobre o Polo">
                <?php else: ?>
                    <div class="polo-icon-card">
                        <span style="font-size: 3rem;">🏛️</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- DIFERENCIAIS -->
    <?php if (!empty($lista_diferenciais)): ?>
    <section class="diferenciais-section">
        <div class="container">
            <h2 class="titulo-secao">Nossos Diferenciais</h2>
            <p class="subtitulo-secao">Conheça os pilares que garantem a excelência no seu aprendizado.</p>

            <div class="grid-diferenciais">
                <?php foreach ($lista_diferenciais as $dif): ?>
                    <div class="card-diferencial">
                        <div class="icon-wrapper">
                            <?php if (!empty($dif['imagem'])): ?>
                                <img src="uploads/<?= htmlspecialchars($dif['imagem']) ?>" alt="<?= htmlspecialchars($dif['titulo']) ?>">
                            <?php else: ?>
                                <span class="icon-fallback">⭐</span>
                            <?php endif; ?>
                        </div>
                        <h3><?= htmlspecialchars($dif['titulo']) ?></h3>
                        <p><?= htmlspecialchars($dif['descricao']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- FORMULÁRIO DE LEADS -->
    <section class="leads-section" id="contato">
        <div class="leads-container">
            <h2>Fale com um <span>Consultor</span></h2>
            <p>Preencha os dados abaixo e tire suas dúvidas sobre nossos cursos.</p>

            <?php if ($msg_lead === 'sucesso'): ?>
                <div class="alert-success">Mensagem enviada com sucesso! Entraremos em contato em breve.</div>
            <?php elseif ($msg_lead === 'erro'): ?>
                <div class="alert-error">Por favor, preencha todos os campos obrigatórios.</div>
            <?php endif; ?>

            <form action="#contato" method="POST" class="leads-form">
                <input type="hidden" name="acao_lead" value="1">

                <div class="form-group-row">
                    <input type="text" name="nome" placeholder="Seu nome completo" required>
                    <input type="email" name="email" placeholder="Seu melhor e-mail" required>
                </div>

                <div class="form-group-row">
                    <input type="tel" name="telefone" placeholder="Telefone / WhatsApp" required>
                    <select name="curso_interesse">
                        <option value="">Selecione o Curso de Interesse</option>
                        <?php foreach ($cursos as $c): ?>
                            <option value="<?= htmlspecialchars($c['titulo']) ?>"><?= htmlspecialchars($c['titulo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-lead">Quero mais informações →</button>
            </form>
        </div>
    </section>

    <!-- RODAPÉ DINÂMICO -->
    <?php include 'footer.php'; ?>

    <!-- SCRIPT DE ALTERNÂNCIA DE TEMA -->
    <script>
        const themeBtn = document.getElementById('themeToggleBtn');
        const moonIcon = document.getElementById('moonIcon');
        const sunIcon = document.getElementById('sunIcon');

        // Verifica preferência salva
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            moonIcon.style.display = 'none';
            sunIcon.style.display = 'block';
        }

        themeBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            
            if (isDark) {
                moonIcon.style.display = 'none';
                sunIcon.style.display = 'block';
                localStorage.setItem('theme', 'dark');
            } else {
                moonIcon.style.display = 'block';
                sunIcon.style.display = 'none';
                localStorage.setItem('theme', 'light');
            }
        });
    </script>
</body>
</html>