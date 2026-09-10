<?php
// Garante o carregamento da conexão usando o caminho absoluto do diretório do footer
require_once __DIR__ . '/conexao.php';

$stmt_foot = $pdo->query("SELECT * FROM rodape WHERE id = 1");
$foot = $stmt_foot->fetch(PDO::FETCH_ASSOC);

$stmt_head = $pdo->query("SELECT * FROM configuracoes WHERE id = 1");
$head = $stmt_head->fetch(PDO::FETCH_ASSOC);
$link_contato = !empty($head['link_contato']) && $head['link_contato'] !== '#' ? $head['link_contato'] : 'contato.php';
$link_quem_somos = !empty($head['link_quem_somos']) && $head['link_quem_somos'] !== '#' ? $head['link_quem_somos'] : 'quem-somos.php';

$stmt_c = $pdo->query("SELECT id, titulo FROM cursos ORDER BY id DESC LIMIT 5");
$cursos_foot = $stmt_c->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .site-footer {
        background-color: #000;
        color: #fff;
        padding: 50px 8% 20px 8%;
        font-family: Arial, sans-serif;
        border-top: 1px solid #222;
    }

    .footer-container {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 30px;
        margin-bottom: 40px;
    }

    .footer-col {
        flex: 1;
        min-width: 200px;
    }

    .footer-col img.main-logo {
        max-width: 180px;
    }

    .footer-col h4 {
        color: #f57c00;
        font-size: 1.3em;
        margin-top: 0;
        margin-bottom: 15px;
    }

    .footer-col ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-col ul li {
        margin-bottom: 8px;
    }

    .footer-col ul li a {
        color: #ccc;
        text-decoration: none;
        font-size: 0.95em;
        transition: color 0.2s;
    }

    .footer-col ul li a:hover {
        color: #f57c00;
    }

    .footer-contacts p {
        margin: 8px 0;
        color: #ccc;
        font-size: 0.95em;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .social-icons {
        display: flex;
        gap: 12px;
        margin-top: 10px;
    }

    .social-icons a {
        color: #fff;
        background: #222;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: background 0.2s;
    }

    .social-icons a:hover {
        background: #f57c00;
    }

    .emec-box {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .emec-box img {
        max-width: 140px;
        border-radius: 4px;
    }

    .footer-bottom {
        border-top: 1px solid #333;
        padding-top: 20px;
        text-align: center;
        color: #f57c00;
        font-size: 0.9em;
    }
</style>

<footer class="site-footer">
    <div class="footer-container">
        
        <!-- Coluna 1: Logo -->
        <div class="footer-col">
            <?php if(!empty($foot['logo_footer'])): ?>
                <img src="uploads/<?= htmlspecialchars($foot['logo_footer']) ?>" class="main-logo" alt="Fenatehd">
            <?php endif; ?>
        </div>

        <!-- Coluna 2: Menu -->
        <div class="footer-col">
            <h4>Menu</h4>
            <ul>
                <li><a href="<?= htmlspecialchars($head['link_home'] ?? 'index.php') ?>">Home</a></li>
                <li><a href="cursos.php">Cursos ▾</a></li>
                <li><a href="<?= htmlspecialchars($head['link_alunos'] ?? '#') ?>">Alunos</a></li>
                <li><a href="<?= htmlspecialchars($head['link_blog'] ?? '#') ?>">Blog</a></li>
                <li><a href="<?= htmlspecialchars($link_contato) ?>">Contato</a></li>
                <li><a href="<?= htmlspecialchars($link_quem_somos) ?>">Quem somos</a></li>
            </ul>
        </div>

        <!-- Coluna 3: Contatos e Social -->
        <div class="footer-col footer-contacts">
            <h4>Contatos</h4>
            <p>📞 <?= htmlspecialchars($foot['telefone'] ?? '') ?></p>
            <p>💬 <?= htmlspecialchars($foot['whatsapp'] ?? '') ?></p>
            <p>✉️ <?= htmlspecialchars($foot['email'] ?? '') ?></p>

            <h4 style="margin-top: 20px;">Social</h4>
            <div class="social-icons">
                <a href="<?= htmlspecialchars($foot['link_facebook'] ?? '#') ?>" target="_blank">f</a>
                <a href="<?= htmlspecialchars($foot['link_instagram'] ?? '#') ?>" target="_blank">📷</a>
            </div>
        </div>

        <!-- Coluna 4: Faculdade & e-MEC -->
        <div class="footer-col">
            <?php if(!empty($foot['logo_faculdade'])): ?>
                <img src="uploads/<?= htmlspecialchars($foot['logo_faculdade']) ?>" style="max-height: 40px; margin-bottom: 15px;" alt="LA Faculdades">
            <?php endif; ?>

            <div class="emec-box">
                <?php if(!empty($foot['qr_code'])): ?>
                    <img src="uploads/<?= htmlspecialchars($foot['qr_code']) ?>" alt="e-MEC QR Code">
                <?php endif; ?>
            </div>
        </div>

    </div>

    <div class="footer-bottom">
        <?= htmlspecialchars($foot['texto_copyright'] ?? 'Fenatehd Educacional® 2026. Todos os direitos reservados.') ?>
    </div>
</footer>