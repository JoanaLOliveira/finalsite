<?php
// Garante o carregamento da conexão utilizando o diretório raiz
require_once __DIR__ . '/conexao.php';

// Busca dados do cabeçalho
$stmt_header = $pdo->query("SELECT * FROM configuracoes WHERE id = 1");
$header_data = $stmt_header->fetch(PDO::FETCH_ASSOC);
$link_contato = !empty($header_data['link_contato']) && $header_data['link_contato'] !== '#' ? $header_data['link_contato'] : 'contato.php';
$link_quem_somos = !empty($header_data['link_quem_somos']) && $header_data['link_quem_somos'] !== '#' ? $header_data['link_quem_somos'] : 'quem-somos.php';

// Busca cursos para o menu dropdown
$stmt_dropdown = $pdo->query("SELECT id, titulo FROM cursos ORDER BY id DESC LIMIT 10");
$lista_cursos = $stmt_dropdown->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .site-header {
        background-color: #000;
        padding: 15px 5%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #222;
        font-family: Arial, sans-serif;
    }

    .header-logos {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .header-logos img {
        height: 45px;
        object-fit: contain;
    }

    .header-nav {
        display: flex;
        align-items: center;
        gap: 25px;
    }

    .header-nav a {
        color: #fff;
        text-decoration: none;
        font-size: 0.95em;
        transition: color 0.2s;
    }

    .header-nav a:hover {
        color: #f57c00;
    }

    /* Menu Dropdown de Cursos */
    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-btn {
        color: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
        font-size: 0.95em;
        transition: color 0.2s;
    }

    .dropdown-btn:hover {
        color: #f57c00;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #111;
        min-width: 240px;
        box-shadow: 0px 8px 16px rgba(0,0,0,0.5);
        z-index: 1000;
        border-radius: 4px;
        top: 100%;
        left: 0;
    }

    .dropdown-content a {
        color: #ccc;
        padding: 12px 16px;
        display: block;
        border-bottom: 1px solid #222;
    }

    .dropdown-content a:hover {
        background-color: #222;
        color: #f57c00;
    }

    .dropdown-content a.ver-todos {
        font-weight: bold;
        color: #f57c00;
        text-align: center;
        background-color: #181818;
    }

    .dropdown-content a.ver-todos:hover {
        background-color: #f57c00;
        color: #fff;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }

    /* Botão Área do Aluno */
    .btn-area-aluno {
        background-color: #e65100;
        color: #fff !important;
        padding: 10px 20px;
        border-radius: 4px;
        font-weight: bold;
        transition: background 0.2s;
    }

    .btn-area-aluno:hover {
        background-color: #ef6c00 !important;
    }
</style>

<header class="site-header">
    <div class="header-logos">
        <?php if(!empty($header_data['logo1'])): ?>
            <a href="index.php">
                <img src="uploads/<?= htmlspecialchars($header_data['logo1']) ?>" alt="Fenatehd">
            </a>
        <?php endif; ?>
        
        <?php if(!empty($header_data['logo2'])): ?>
            <a href="index.php">
                <img src="uploads/<?= htmlspecialchars($header_data['logo2']) ?>" alt="LA Faculdades">
            </a>
        <?php endif; ?>
    </div>

    <nav class="header-nav">
        <a href="<?= htmlspecialchars($header_data['link_home'] ?? 'index.php') ?>">Home</a>
        
        <!-- Dropdown ligado ao catálogo (cursos.php) e detalhes (curso.php) -->
        <div class="dropdown">
            <a href="cursos.php" class="dropdown-btn">Cursos ▾</a>
            <div class="dropdown-content">
                <?php foreach($lista_cursos as $c): ?>
                    <a href="curso.php?id=<?= $c['id'] ?>"><?= htmlspecialchars($c['titulo']) ?></a>
                <?php endforeach; ?>
                <a href="cursos.php" class="ver-todos">Ver Todos os Cursos →</a>
            </div>
        </div>

        <a href="<?= htmlspecialchars($header_data['link_alunos'] ?? '#') ?>">Alunos</a>
        <a href="<?= htmlspecialchars($header_data['link_blog'] ?? '#') ?>">Blog</a>
        <a href="<?= htmlspecialchars($link_contato) ?>">Contato</a>
        <a href="<?= htmlspecialchars($link_quem_somos) ?>">Quem somos</a>
        <a href="<?= htmlspecialchars($header_data['link_area_aluno'] ?? '#') ?>" class="btn-area-aluno">Área do Aluno</a>
    </nav>
</header>