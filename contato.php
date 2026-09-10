<?php
// Inclui a conexão com o banco de dados
require_once __DIR__ . '/conexao.php';

$msg_status = '';

// Processamento do Formulário de Captação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_lead'])) {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $curso = trim($_POST['curso'] ?? '');

    if (!empty($nome) && !empty($email) && !empty($telefone)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO leads (nome, email, telefone, curso) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $telefone, $curso]);
            $msg_status = "<div class='alerta-sucesso'>Mensagem e cadastro enviados com sucesso! Em breve um consultor entrará em contato.</div>";
        } catch (PDOException $e) {
            $msg_status = "<div class='alerta-erro'>Erro ao enviar cadastro: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        $msg_status = "<div class='alerta-erro'>Por favor, preencha todos os campos obrigatórios.</div>";
    }
}

// Busca cursos para o select do formulário
$cursos_opcoes = [];
try {
    $stmt_c = $pdo->query("SELECT titulo FROM cursos ORDER BY titulo ASC");
    if ($stmt_c) {
        $cursos_opcoes = $stmt_c->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {}

// Busca dados do rodapé/contato
$rodape = [];
try {
    $stmt_r = $pdo->query("SELECT * FROM rodape WHERE id = 1");
    if ($stmt_r) {
        $rodape = $stmt_r->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {}

// Inclui o Cabeçalho Global
require_once __DIR__ . '/header.php';
?>

<style>
    body {
        background-color: #f8f9fa;
        color: #212529;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
    }

    /* Estilos específicos da página de Contato */
    .header-contato {
        background: linear-gradient(135deg, #111 0%, #1a1a1a 100%);
        color: #fff;
        text-align: center;
        padding: 50px 20px;
    }
    .header-contato h1 { font-size: 2.2em; font-weight: 800; color: #fff; margin-bottom: 10px; }
    .header-contato p { font-size: 1.1em; color: #ccc; }
    .linha-laranja { width: 60px; height: 4px; background-color: #f57c00; margin: 15px auto; border-radius: 2px; }

    .container-contato {
        max-width: 1200px;
        margin: 40px auto 60px auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }

    /* Card dos Consultores e Canais */
    .card-canais {
        background: #fff;
        padding: 35px;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    .card-canais h2 { font-size: 1.5em; color: #111; margin-bottom: 20px; border-bottom: 2px solid #f57c00; padding-bottom: 10px; }
    
    .secao-bloco { margin-bottom: 30px; }
    .secao-bloco h3 { font-size: 1.1em; color: #555; margin-bottom: 15px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; }

    /* Botões de WhatsApp dos Consultores */
    .grid-consultores {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .btn-whatsapp {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #25d366;
        color: #fff;
        padding: 14px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        font-size: 1em;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(37, 211, 102, 0.2);
    }
    .btn-whatsapp:hover {
        background-color: #1eb954;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(37, 211, 102, 0.3);
    }
    .btn-whatsapp span.cargo { font-size: 0.85em; opacity: 0.9; font-weight: normal; }

    /* Botões de E-mail */
    .btn-email {
        display: flex;
        align-items: center;
        gap: 12px;
        background-color: #111;
        color: #fff;
        padding: 14px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        font-size: 0.95em;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }
    .btn-email:hover {
        background-color: #f57c00;
        transform: translateY(-2px);
    }

    /* Formulário de Captação */
    .card-form {
        background: #fff;
        padding: 35px;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        border-top: 5px solid #f57c00;
    }
    .card-form h2 { font-size: 1.5em; color: #111; margin-bottom: 8px; }
    .card-form p.sub-form { color: #666; font-size: 0.95em; margin-bottom: 25px; }

    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 8px; font-size: 0.9em; color: #444; }
    .form-group input, .form-group select {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 0.95em;
        transition: border-color 0.3s;
    }
    .form-group input:focus, .form-group select:focus {
        border-color: #f57c00;
        outline: none;
    }

    .btn-submit {
        width: 100%;
        background-color: #f57c00;
        color: #fff;
        border: none;
        padding: 15px;
        border-radius: 6px;
        font-size: 1.05em;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .btn-submit:hover { background-color: #e65100; }

    /* Status Messages */
    .alerta-sucesso { background: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #c8e6c9; }
    .alerta-erro { background: #ffebee; color: #c62828; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #ffcdd2; }

    /* Responsividade */
    @media (max-width: 850px) {
        .container-contato { grid-template-columns: 1fr; margin-top: 20px; }
    }
</style>

<header class="header-contato">
    <h1>FALE CONOSCO</h1>
    <div class="linha-laranja"></div>
    <p>Estamos prontos para tirar suas dúvidas e orientar sua jornada acadêmica.</p>
</header>

<main class="container-contato">
    
    <!-- COLUNA 1: Atendimento via WhatsApp e E-mails -->
    <div class="card-canais">
        <h2>Nossos Canais Diretos</h2>

        <!-- Consultores WhatsApp -->
        <div class="secao-bloco">
            <h3>Fale com Nossos Consultores</h3>
            <div class="grid-consultores">
                
                <!-- Consultor 1 -->
                <a href="https://wa.me/5500000000000?text=Olá,%20gostaria%20de%20saber%20mais%20sobre%20os%20cursos!" target="_blank" class="btn-whatsapp">
                    <div>
                        💬 Consultor Acadêmico 01
                        <br><span class="cargo">Atendimento Geral / Matrículas</span>
                    </div>
                    ➔
                </a>

                <!-- Consultor 2 -->
                <a href="https://wa.me/5500000000000?text=Olá,%20preciso%20de%20informações%20sobre%20Pós-Graduação!" target="_blank" class="btn-whatsapp">
                    <div>
                        💬 Consultor Acadêmico 02
                        <br><span class="cargo">Especialista em Pós-Graduação</span>
                    </div>
                    ➔
                </a>

                <!-- WhatsApp Principal do Banco -->
                <?php if (!empty($rodape['whatsapp'])): 
                    $num_clean = preg_replace('/[^0-9]/', '', $rodape['whatsapp']);
                ?>
                    <a href="https://wa.me/55<?= $num_clean ?>?text=Olá,%20vim%20pelo%20site!" target="_blank" class="btn-whatsapp" style="background-color: #128c7e;">
                        <div>
                            📲 Central de Atendimento
                            <br><span class="cargo"><?= htmlspecialchars($rodape['whatsapp']) ?></span>
                        </div>
                        ➔
                    </a>
                <?php endif; ?>

            </div>
        </div>

        <!-- E-mails de Contato -->
        <div class="secao-bloco">
            <h3>E-mails de Atendimento</h3>
            
            <a href="mailto:<?= htmlspecialchars($rodape['email'] ?? 'contato@seusite.com.br') ?>" class="btn-email">
                ✉️ <?= htmlspecialchars($rodape['email'] ?? 'contato@seusite.com.br') ?>
            </a>

            <a href="mailto:secretaria@seusite.com.br" class="btn-email">
                ✉️ secretaria@seusite.com.br (Secretaria Acadêmica)
            </a>
        </div>

    </div>

    <!-- COLUNA 2: Formulário de Captação (Leads) -->
    <div class="card-form">
        <h2>Solicite um Contato</h2>
        <p class="sub-form">Preencha o formulário abaixo para receber o atendimento de nossa equipe.</p>

        <?= $msg_status ?>

        <form action="contato.php" method="POST">
            <input type="hidden" name="acao_lead" value="1">

            <div class="form-group">
                <label>Seu Nome Completo *</label>
                <input type="text" name="nome" required placeholder="Ex: Maria Silva">
            </div>

            <div class="form-group">
                <label>Seu E-mail *</label>
                <input type="email" name="email" required placeholder="Ex: maria@email.com">
            </div>

            <div class="form-group">
                <label>Telefone / WhatsApp *</label>
                <input type="text" name="telefone" required placeholder="Ex: (00) 90000-0000">
            </div>

            <div class="form-group">
                <label>Curso de Interesse</label>
                <select name="curso">
                    <option value="">-- Selecione um curso (opcional) --</option>
                    <?php foreach ($cursos_opcoes as $c): ?>
                        <option value="<?= htmlspecialchars($c['titulo']) ?>"><?= htmlspecialchars($c['titulo']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn-submit">Enviar e Receber Contato</button>
        </form>
    </div>

</main>

<?php
// Inclui o Rodapé Global
require_once __DIR__ . '/footer.php';
?>