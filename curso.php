<?php
require_once 'conexao.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

// 1. Busca os dados do curso atual
$stmt = $pdo->prepare("SELECT * FROM cursos WHERE id = ?");
$stmt->execute([$id]);
$curso = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$curso) {
    die("Curso não encontrado.");
}

// Transformar textos separados por vírgula em arrays
$conteudos = array_filter(explode(',', $curso['conteudos'] ?? ''));
$publicos = array_filter(explode(',', $curso['publico_alvo'] ?? ''));

// 2. Busca cursos recomendados da mesma categoria (mesmo 'tipo'), excluindo o curso atual
$stmt_relacionados = $pdo->prepare("SELECT * FROM cursos WHERE tipo = ? AND id != ? ORDER BY id DESC LIMIT 3");
$stmt_relacionados->execute([$curso['tipo'], $id]);
$cursos_relacionados = $stmt_relacionados->fetchAll(PDO::FETCH_ASSOC);

// Fallback: Se não houver cursos da mesma categoria, busca outros cursos recentes
if (empty($cursos_relacionados)) {
    $stmt_fallback = $pdo->prepare("SELECT * FROM cursos WHERE id != ? ORDER BY id DESC LIMIT 3");
    $stmt_fallback->execute([$id]);
    $cursos_relacionados = $stmt_fallback->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($curso['titulo']) ?> - Fenatehd Educacional</title>
    
    <!-- Fonte Moderna Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        /* ==========================================================================
           1. SISTEMA DE TEMAS (CSS VARIABLES)
           ========================================================================== */
        :root {
            /* Tema Claro (Padrão) */
            --bg-main: #f8fafc;
            --bg-hero: #ffffff;
            --bg-card: #ffffff;
            --bg-card-subtle: #f1f5f9;
            --bg-relacionados: #f1f5f9;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --border-color: #e2e8f0;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);

            /* Destaque / Brand */
            --primary: #f57c00;
            --primary-hover: #e65100;
            --primary-light: rgba(245, 124, 0, 0.1);
        }

        /* Tema Escuro */
        [data-theme="dark"] {
            --bg-main: #0d0d0d;
            --bg-hero: #000000;
            --bg-card: #141414;
            --bg-card-subtle: #1a1a1a;
            --bg-relacionados: #050505;
            --text-primary: #ffffff;
            --text-secondary: #94a3b8;
            --border-color: #222222;
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        body { 
            background-color: var(--bg-main); 
            color: var(--text-primary); 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, Arial, sans-serif; 
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ==========================================================================
           2. HERO SECTION / DADOS PRINCIPAIS DO CURSO
           ========================================================================== */
        .hero-curso { 
            background-color: var(--bg-hero); 
            padding: 60px 20px; 
            border-bottom: 1px solid var(--border-color);
        }

        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 40px;
        }

        .hero-info { 
            flex: 1; 
        }

        .btn-voltar { 
            background: transparent; 
            color: var(--primary); 
            border: 1px solid var(--primary);
            text-decoration: none; 
            padding: 8px 18px; 
            border-radius: 8px; 
            display: inline-flex; 
            align-items: center;
            gap: 8px;
            margin-bottom: 25px; 
            font-weight: 600; 
            font-size: 0.9em;
            transition: all 0.3s ease;
        }

        .btn-voltar:hover {
            background: var(--primary);
            color: #ffffff;
            transform: translateX(-3px);
        }

        .badge-container {
            margin-bottom: 15px;
        }

        .badge { 
            background: var(--primary-hover); 
            color: #ffffff; 
            padding: 5px 12px; 
            border-radius: 50px; 
            font-size: 0.78em; 
            text-transform: uppercase; 
            font-weight: 800; 
            letter-spacing: 0.8px;
            display: inline-block;
        }

        .hero-info h1 { 
            font-size: 2.5em; 
            font-weight: 800;
            margin: 10px 0 20px 0; 
            line-height: 1.2;
            color: var(--text-primary);
        }

        .hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            background: var(--bg-card);
            padding: 18px 24px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: var(--card-shadow);
        }

        .hero-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95em;
            color: var(--text-secondary);
        }

        .hero-meta-item strong {
            color: var(--text-primary);
        }

        .hero-img {
            flex-shrink: 0;
        }

        .hero-img img { 
            width: 360px; 
            max-width: 100%;
            height: auto;
            border-radius: 16px; 
            border: 2px solid var(--primary); 
            box-shadow: 0 10px 30px rgba(245, 124, 0, 0.25);
            display: block;
            object-fit: cover;
        }

        /* ==========================================================================
           3. ESTRUTURA PRINCIPAL (MAIN CONTENT & SIDEBAR)
           ========================================================================== */
        .container-curso { 
            display: flex; 
            gap: 35px; 
            max-width: 1200px;
            margin: 0 auto;
            padding: 50px 20px; 
            flex-grow: 1;
            width: 100%;
        }

        .main-content { 
            flex: 2; 
            display: flex; 
            flex-direction: column; 
            gap: 30px; 
        }

        .sidebar { 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
            gap: 25px; 
            position: sticky;
            top: 30px;
            height: fit-content;
        }

        /* CARDS DO CONTEÚDO PRINCIPAL */
        .card-conteudo { 
            background: var(--bg-card); 
            color: var(--text-primary); 
            border-radius: 16px; 
            padding: 32px; 
            border: 1px solid var(--border-color);
            border-left: 5px solid var(--primary); 
            box-shadow: var(--card-shadow);
        }

        .card-conteudo h3 { 
            margin-bottom: 18px; 
            color: var(--text-primary); 
            font-size: 1.4em;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-conteudo p {
            color: var(--text-secondary);
            font-size: 1em;
            line-height: 1.7;
        }

        /* LISTA DE MÓDULOS / CONTEÚDO PROGRAMÁTICO */
        .module-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 15px;
        }

        .module-item { 
            background: var(--bg-card-subtle); 
            border: 1px solid var(--border-color); 
            padding: 16px 20px; 
            border-radius: 10px; 
            color: var(--text-primary); 
            font-weight: 500; 
            font-size: 0.98em;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: border-color 0.25s ease;
        }

        .module-item:hover {
            border-color: var(--primary);
        }

        .module-number { 
            background: var(--primary); 
            color: #ffffff; 
            width: 28px;
            height: 28px;
            border-radius: 50%; 
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85em;
            flex-shrink: 0;
        }

        /* SIDEBAR CARDS (INVESTIMENTO & SUPORTE) */
        .card-dark { 
            background: var(--bg-card); 
            color: var(--text-primary); 
            border-radius: 16px; 
            padding: 30px 24px; 
            text-align: center; 
            border: 1px solid var(--border-color); 
            box-shadow: var(--card-shadow);
        }

        .card-dark h3 {
            font-size: 1.3em;
            font-weight: 800;
        }

        .btn-inscreva { 
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-hover) 100%); 
            color: #ffffff; 
            text-decoration: none; 
            padding: 16px; 
            display: block; 
            border-radius: 10px; 
            font-weight: 800; 
            font-size: 1.1em;
            margin-top: 20px; 
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(245, 124, 0, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-inscreva:hover {
            opacity: 0.95;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 124, 0, 0.45);
        }

        /* PÚBLICO-ALVO */
        .card-publico {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 30px 24px;
            border: 1px solid var(--border-color);
            box-shadow: var(--card-shadow);
        }

        .card-publico h3 {
            margin-bottom: 20px;
            font-size: 1.25em;
            font-weight: 700;
            color: var(--text-primary);
        }

        .publico-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .list-box { 
            background: var(--primary-light); 
            border: 1px solid rgba(245, 124, 0, 0.25); 
            color: var(--primary); 
            padding: 12px 16px; 
            border-radius: 8px; 
            font-weight: 600; 
            font-size: 0.92em;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-whatsapp {
            background: #25D366; 
            color: #ffffff; 
            padding: 14px; 
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: 700;
            font-size: 0.95em;
            margin-top: 15px;
            transition: all 0.3s ease;
        }

        .btn-whatsapp:hover {
            background: #20ba5a;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.3);
        }

        /* ==========================================================================
           4. SEÇÃO CURSOS RELACIONADOS / SUGESTÕES
           ========================================================================== */
        .relacionados-section {
            background-color: var(--bg-relacionados);
            padding: 70px 20px;
            border-top: 1px solid var(--border-color);
        }

        .relacionados-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .relacionados-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .relacionados-header h2 {
            font-size: 2rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .relacionados-header p {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        .relacionados-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        .curso-link {
            text-decoration: none;
            color: inherit;
            display: flex;
        }

        .curso-card { 
            background: var(--bg-card); 
            color: var(--text-primary); 
            border-radius: 16px; 
            width: 100%;
            overflow: hidden; 
            border: 1px solid var(--border-color);
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .curso-card:hover {
            transform: translateY(-8px);
            border-color: var(--primary);
            box-shadow: 0 15px 35px rgba(245, 124, 0, 0.15);
        }

        .curso-card-img-wrapper {
            width: 100%;
            height: 180px;
            overflow: hidden;
            background-color: #000;
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
            padding: 22px; 
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .curso-info h3 { 
            margin: 0 0 12px 0; 
            font-size: 1.1em; 
            font-weight: 700;
            color: var(--text-primary); 
            line-height: 1.35;
            min-height: 2.7em;
        }

        .curso-preco { 
            font-weight: 600; 
            color: var(--text-secondary); 
            font-size: 0.88em; 
            margin-top: auto; 
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: baseline;
            gap: 5px;
        }

        .curso-preco span {
            color: var(--primary);
            font-size: 1.25em;
            font-weight: 800;
        }

        /* Botão Alternador de Tema */
        .theme-toggle-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: var(--bg-card);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            padding: 12px 18px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 1000;
        }

        /* ==========================================================================
           5. RESPONSIVIDADE
           ========================================================================== */
        @media (max-width: 900px) {
            .hero-container {
                flex-direction: column;
                text-align: center;
            }

            .hero-img img {
                width: 100%;
                max-width: 400px;
            }

            .hero-meta {
                justify-content: center;
            }

            .container-curso {
                flex-direction: column;
            }

            .sidebar {
                position: static;
            }
        }

        @media (max-width: 600px) {
            .hero-info h1 {
                font-size: 1.8em;
            }
            .hero-meta {
                flex-direction: column;
                align-items: flex-start;
            }
            .card-conteudo {
                padding: 22px;
            }
        }
    </style>
</head>
<body>

    <!-- CABEÇALHO DINÂMICO -->
    <?php include 'header.php'; ?>

    <!-- HERO DO CURSO -->
    <div class="hero-curso">
        <div class="hero-container">
            <div class="hero-info">
                <a href="cursos.php" class="btn-voltar">← Voltar para cursos</a>
                
                <div class="badge-container">
                    <span class="badge"><?= htmlspecialchars($curso['tipo'] ?? 'Curso') ?></span>
                </div>

                <h1><?= htmlspecialchars($curso['titulo']) ?></h1>

                <div class="hero-meta">
                    <div class="hero-meta-item">
                        <span>🕒</span>
                        <span><strong>Duração:</strong> <?= htmlspecialchars($curso['duracao'] ?? 'Flexível') ?></span>
                    </div>
                    <div class="hero-meta-item">
                        <span>💻</span>
                        <span><strong>Modalidade:</strong> <?= htmlspecialchars($curso['modalidade'] ?? 'EAD') ?></span>
                    </div>
                    <div class="hero-meta-item">
                        <span>🏷️</span>
                        <span><strong>A partir de:</strong> <strong style="color: var(--primary);">R$ <?= number_format($curso['preco'], 2, ',', '.') ?></strong></span>
                    </div>
                </div>
            </div>

            <div class="hero-img">
                <img src="uploads/<?= htmlspecialchars(basename($curso['imagem'])) ?>" alt="<?= htmlspecialchars($curso['titulo']) ?>">
            </div>
        </div>
    </div>

    <!-- CONTEÚDO PRINCIPAL E SIDEBAR -->
    <div class="container-curso">
        
        <!-- COLUNA DA ESQUERDA (DADOS DO CURSO) -->
        <div class="main-content">
            <div class="card-conteudo">
                <h3>📖 Sobre o curso</h3>
                <p><?= nl2br(htmlspecialchars($curso['descricao'])) ?></p>
            </div>

            <?php if (!empty($curso['materiais_metodologia'])): ?>
                <div class="card-conteudo">
                    <h3>🛠️ Materiais e Metodologia</h3>
                    <p><?= nl2br(htmlspecialchars($curso['materiais_metodologia'])) ?></p>
                </div>
            <?php endif; ?>

            <div class="card-conteudo">
                <h3>📋 Conteúdo do curso</h3>
                <?php if (!empty($conteudos)): ?>
                    <div class="module-list">
                        <?php foreach ($conteudos as $index => $item): ?>
                            <div class="module-item">
                                <span class="module-number"><?= $index + 1 ?></span>
                                <span><?= htmlspecialchars(trim($item)) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-secondary); margin-top: 10px;">Conteúdo em breve.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- COLUNA DA DIREITA (SIDEBAR FIXA) -->
        <div class="sidebar">
            <div class="card-dark">
                <h3 style="color: var(--primary); margin-bottom: 5px;">INVESTIMENTO ÚNICO</h3>
                <p style="font-size: 0.9em; color: var(--text-secondary);">Início Imediato • Matricule-se Hoje</p>
                <a href="matricula.php?curso_id=<?= $curso['id'] ?>" class="btn-inscreva">► Inscreva-se Já!</a>
            </div>

            <div class="card-publico">
                <h3>🎯 Público-alvo</h3>
                <div class="publico-list">
                    <?php if (!empty($publicos)): ?>
                        <?php foreach ($publicos as $item): ?>
                            <div class="list-box">
                                <span>✓</span>
                                <span><?= htmlspecialchars(trim($item)) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: var(--text-secondary); font-size: 0.9em;">Consulte os requisitos do curso.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-dark">
                <h3>Suporte Acadêmico</h3>
                <p style="font-size: 0.88em; color: var(--text-secondary); margin-top: 6px;">Dúvidas sobre a grade ou inscrições?</p>
                <a href="https://wa.me/" target="_blank" class="btn-whatsapp">
                    <span>💬</span> Falar no WhatsApp
                </a>
            </div>
        </div>

    </div>

    <!-- SEÇÃO CURSOS RELACIONADOS / RECOMENDADOS -->
    <?php if (!empty($cursos_relacionados)): ?>
        <section class="relacionados-section">
            <div class="relacionados-container">
                <div class="relacionados-header">
                    <h2>Cursos Relacionados</h2>
                    <div style="width: 50px; height: 3px; background-color: var(--primary); margin: 10px auto; border-radius: 2px;"></div>
                    <p>Confira outras formações que também podem impulsionar sua carreira</p>
                </div>

                <div class="relacionados-grid">
                    <?php foreach ($cursos_relacionados as $rel): ?>
                        <a href="curso.php?id=<?= $rel['id'] ?>" class="curso-link">
                            <div class="curso-card">
                                <div class="curso-card-img-wrapper">
                                    <img src="uploads/<?= htmlspecialchars(basename($rel['imagem'])) ?>" alt="<?= htmlspecialchars($rel['titulo']) ?>">
                                </div>
                                <div class="curso-info">
                                    <h3><?= htmlspecialchars($rel['titulo']) ?></h3>
                                    <div class="curso-preco">
                                        A partir de R$ <span><?= number_format($rel['preco'], 2, ',', '.') ?></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- BOTÃO FLUTUANTE DE ALTERNAR TEMA -->
    <button class="theme-toggle-btn" id="themeToggleBtn">
        <span id="themeIcon">🌙</span>
        <span id="themeText">Modo Escuro</span>
    </button>

    <!-- RODAPÉ DINÂMICO -->
    <?php include 'footer.php'; ?>

    <!-- SCRIPT DE GERENCIAMENTO DE TEMA -->
    <script>
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const themeText = document.getElementById('themeText');

        // Função para aplicar o tema correto
        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);

            if (theme === 'dark') {
                themeIcon.textContent = '☀️';
                themeText.textContent = 'Modo Claro';
            } else {
                themeIcon.textContent = '🌙';
                themeText.textContent = 'Modo Escuro';
            }
        }

        // Detecta o tema salvo no localStorage (do index ou cursos) ou preferência do sistema
        const savedTheme = localStorage.getItem('theme') || 
            (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

        applyTheme(savedTheme);

        // Alterna o tema ao clicar
        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
        });
    </script>
</body>
</html>