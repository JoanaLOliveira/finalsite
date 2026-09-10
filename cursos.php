<?php
require_once 'conexao.php';

// Captura filtros da URL
$busca = trim($_GET['busca'] ?? '');
$tipo_filtro = trim($_GET['tipo'] ?? '');

// Monta a consulta dinâmica SQL
$sql = "SELECT * FROM cursos WHERE 1=1";
$params = [];

if (!empty($busca)) {
    $sql .= " AND (titulo LIKE ? OR descricao LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

if (!empty($tipo_filtro)) {
    $sql .= " AND tipo = ?";
    $params[] = $tipo_filtro;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca os tipos/categorias únicos disponíveis no banco para o filtro
$stmt_tipos = $pdo->query("SELECT DISTINCT tipo FROM cursos WHERE tipo IS NOT NULL AND tipo != '' ORDER BY tipo ASC");
$tipos_disponiveis = $stmt_tipos->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="pt-br" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Cursos - Fenatehd Educacional</title>
    
    <!-- Fonte Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        /* ==========================================================================
           1. VARIÁVEIS DE TEMA (LIGHT & DARK)
           ========================================================================== */
        :root {
            /* Tema Claro (Light) */
            --bg-main: #f8fafc;
            --bg-header: #ffffff;
            --bg-card: #ffffff;
            --bg-input: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);

            /* Brand / Destaque */
            --primary: #f57c00;
            --primary-hover: #e65100;
            --primary-light: rgba(245, 124, 0, 0.1);
        }

        [data-theme="dark"] {
            /* Tema Escuro (Dark) */
            --bg-main: #0d0d0d;
            --bg-header: #141414;
            --bg-card: #141414;
            --bg-input: #1a1a1a;
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ==========================================================================
           2. HERO & CABEÇALHO DO CATÁLOGO
           ========================================================================== */
        .catalog-hero {
            background-color: var(--bg-header);
            border-bottom: 1px solid var(--border-color);
            padding: 60px 20px;
            text-align: center;
        }

        .catalog-hero-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .catalog-hero h1 {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 12px;
            color: var(--text-primary);
            letter-spacing: -0.5px;
        }

        .catalog-hero p {
            font-size: 1.15rem;
            color: var(--text-secondary);
            margin-bottom: 35px;
        }

        /* Barra de Busca e Filtros */
        .filter-form {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            max-width: 750px;
            margin: 0 auto;
        }

        .search-input, .select-input {
            background: var(--bg-input);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            padding: 14px 20px;
            border-radius: 10px;
            font-size: 1rem;
            outline: none;
        }

        .search-input {
            flex: 2;
            min-width: 240px;
        }

        .search-input:focus, .select-input:focus {
            border-color: var(--primary);
        }

        .select-input {
            flex: 1;
            min-width: 180px;
            cursor: pointer;
        }

        .btn-filtrar {
            background: var(--primary);
            color: #ffffff;
            border: none;
            padding: 14px 28px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-filtrar:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }

        .btn-limpar {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            padding: 14px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-limpar:hover {
            color: var(--text-primary);
            border-color: var(--text-secondary);
        }

        /* ==========================================================================
           3. GRADE DE CURSOS
           ========================================================================== */
        .catalog-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
            flex-grow: 1;
            width: 100%;
        }

        .results-count {
            margin-bottom: 25px;
            color: var(--text-secondary);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .cursos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }

        .curso-link {
            text-decoration: none;
            color: inherit;
            display: flex;
        }

        .curso-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            overflow: hidden;
            width: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        }

        .curso-card:hover {
            transform: translateY(-8px);
            border-color: var(--primary);
            box-shadow: 0 15px 35px rgba(245, 124, 0, 0.15);
        }

        .curso-img-wrapper {
            position: relative;
            width: 100%;
            height: 200px;
            background-color: #000;
            overflow: hidden;
        }

        .curso-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .curso-card:hover .curso-img-wrapper img {
            transform: scale(1.05);
        }

        .curso-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--primary);
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .curso-body {
            padding: 24px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .curso-body h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 12px;
            line-height: 1.35;
        }

        .curso-desc {
            color: var(--text-secondary);
            font-size: 0.92rem;
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .curso-meta-info {
            display: flex;
            gap: 15px;
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 20px;
            margin-top: auto;
        }

        .curso-meta-info span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .curso-footer {
            padding-top: 16px;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .curso-preco {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .curso-preco strong {
            display: block;
            font-size: 1.3rem;
            color: var(--primary);
            font-weight: 800;
        }

        .btn-card-detalhes {
            background: var(--primary-light);
            color: var(--primary);
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .curso-card:hover .btn-card-detalhes {
            background: var(--primary);
            color: #ffffff;
        }

        /* ESTADO VAZIO / SEM RESULTADOS */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--bg-card);
            border-radius: 16px;
            border: 1px solid var(--border-color);
        }

        .empty-state h3 {
            font-size: 1.4rem;
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        .empty-state p {
            color: var(--text-secondary);
            margin-bottom: 20px;
        }

        /* Botão Flutuante de Tema */
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

        @media (max-width: 768px) {
            .catalog-hero h1 { font-size: 2rem; }
            .filter-form { flex-direction: column; }
            .search-input, .select-input, .btn-filtrar { width: 100%; }
        }
    </style>
</head>
<body>

    <!-- CABEÇALHO DINÂMICO -->
    <?php include 'header.php'; ?>

    <!-- HERO DO CATÁLOGO DE CURSOS -->
    <header class="catalog-hero">
        <div class="catalog-hero-container">
            <h1>Explore Nossos Cursos</h1>
            <p>Invista no seu futuro com capacitações reconhecidas no mercado de trabalho</p>

            <!-- FORMULÁRIO DE FILTRO E PESQUISA -->
            <form method="GET" action="cursos.php" class="filter-form">
                <input 
                    type="text" 
                    name="busca" 
                    class="search-input" 
                    placeholder="O que você quer aprender hoje?" 
                    value="<?= htmlspecialchars($busca) ?>"
                >

                <select name="tipo" class="select-input">
                    <option value="">Todas as Categorias</option>
                    <?php foreach ($tipos_disponiveis as $tipo): ?>
                        <option value="<?= htmlspecialchars($tipo) ?>" <?= $tipo_filtro === $tipo ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tipo) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn-filtrar">Buscar</button>

                <?php if (!empty($busca) || !empty($tipo_filtro)): ?>
                    <a href="cursos.php" class="btn-limpar">Limpar Filtros</a>
                <?php endif; ?>
            </form>
        </div>
    </header>

    <!-- LISTAGEM DOS CURSOS -->
    <main class="catalog-section">
        
        <div class="results-count">
            Mostrando <strong><?= count($cursos) ?></strong> curso(s) disponível(is)
        </div>

        <?php if (!empty($cursos)): ?>
            <div class="cursos-grid">
                <?php foreach ($cursos as $curso): ?>
                    <a href="curso.php?id=<?= $curso['id'] ?>" class="curso-link">
                        <article class="curso-card">
                            <div class="curso-img-wrapper">
                                <span class="curso-badge"><?= htmlspecialchars($curso['tipo'] ?? 'Curso') ?></span>
                                <img src="uploads/<?= htmlspecialchars(basename($curso['imagem'])) ?>" alt="<?= htmlspecialchars($curso['titulo']) ?>">
                            </div>
                            
                            <div class="curso-body">
                                <h3><?= htmlspecialchars($curso['titulo']) ?></h3>
                                <p class="curso-desc"><?= htmlspecialchars($curso['descricao']) ?></p>

                                <div class="curso-meta-info">
                                    <span>🕒 <?= htmlspecialchars($curso['duracao'] ?? 'Flexível') ?></span>
                                    <span>💻 <?= htmlspecialchars($curso['modalidade'] ?? 'EAD') ?></span>
                                </div>

                                <div class="curso-footer">
                                    <div class="curso-preco">
                                        Investimento
                                        <strong>R$ <?= number_format($curso['preco'], 2, ',', '.') ?></strong>
                                    </div>
                                    <span class="btn-card-detalhes">Ver detalhes →</span>
                                </div>
                            </div>
                        </article>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>Nenhum curso encontrado</h3>
                <p>Não encontramos nenhum resultado para a sua busca. Tente buscar por outros termos ou categorias.</p>
                <a href="cursos.php" class="btn-filtrar" style="text-decoration: none; display: inline-block;">Ver Todos os Cursos</a>
            </div>
        <?php endif; ?>

    </main>

    <!-- BOTÃO FLUTUANTE DE TEMA -->
    <button class="theme-toggle-btn" id="themeToggleBtn">
        <span id="themeIcon">🌙</span>
        <span id="themeText">Modo Escuro</span>
    </button>

    <!-- RODAPÉ DINÂMICO -->
    <?php include 'footer.php'; ?>

    <!-- JS DE GERENCIAMENTO DE TEMA -->
    <script>
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const themeText = document.getElementById('themeText');

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

        // Lê a preferência do usuário do localStorage
        const savedTheme = localStorage.getItem('theme') || 
            (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

        applyTheme(savedTheme);

        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
        });
    </script>
</body>
</html>