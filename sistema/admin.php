<?php
require_once __DIR__ . '/auth.php';
exigir_login();

// Inclui a conexão da raiz do projeto
require_once __DIR__ . '/../conexao.php';

$aba = $_GET['aba'] ?? 'leads';

$mensagem_curso = '';
$mensagem_cabecalho = '';
$mensagem_rodape = '';
$mensagem_sobre = '';
$msg_vantagem = '';
$mensagem_banner = '';
$msg_professor = '';

// Diretório de uploads na raiz do projeto
$upload_dir = __DIR__ . '/../uploads/';

// --- PROCESSAMENTO: PROFESSORES ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_professor'])) {
    $id = $_POST['id_professor'] ?? null;
    $nome = trim($_POST['nome']);
    $titulacao = trim($_POST['titulacao']);
    $biografia = trim($_POST['biografia']);
    
    $foto = $_POST['foto_atual'] ?? '';
    if (!empty($_FILES['foto']['name'])) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = time() . '_prof_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $foto);
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE professores SET nome = ?, titulacao = ?, biografia = ?, foto = ? WHERE id = ?");
        $stmt->execute([$nome, $titulacao, $biografia, $foto, $id]);
        $msg_professor = "Professor atualizado com sucesso!";
    } else {
        if (!empty($foto)) {
            $stmt = $pdo->prepare("INSERT INTO professores (nome, titulacao, biografia, foto) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $titulacao, $biografia, $foto]);
            $msg_professor = "Professor cadastrado com sucesso!";
        } else {
            $msg_professor = "Por favor, envie uma foto do professor.";
        }
    }
}

// Excluir Professor
if (isset($_GET['excluir_professor'])) {
    $id_excluir = (int)$_GET['excluir_professor'];
    $stmt = $pdo->prepare("DELETE FROM professores WHERE id = ?");
    $stmt->execute([$id_excluir]);
    header("Location: admin.php?aba=professores");
    exit;
}

// --- DEMAIS PROCESSAMENTOS DA SUA APLICAÇÃO ---

// Banner Principal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_banner'])) {
    $badge = trim($_POST['badge']);
    $titulo = trim($_POST['titulo']);
    $subtitulo = trim($_POST['subtitulo']);
    $descricao = trim($_POST['descricao']);

    $stmt_check = $pdo->query("SELECT id FROM banner_principal WHERE id = 1");
    if ($stmt_check->fetch()) {
        $sql = "UPDATE banner_principal SET badge = ?, titulo = ?, subtitulo = ?, descricao = ? WHERE id = 1";
    } else {
        $sql = "INSERT INTO banner_principal (id, badge, titulo, subtitulo, descricao) VALUES (1, ?, ?, ?, ?)";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$badge, $titulo, $subtitulo, $descricao]);
    $mensagem_banner = "Banner Principal atualizado com sucesso!";
}

// Cadastrar / Editar Curso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_curso'])) {
    $id_curso = $_POST['id_curso'] ?? null;
    $titulo = trim($_POST['titulo']);
    
    $tipo = $_POST['tipo'];
    if ($tipo === 'OUTRO' && !empty($_POST['tipo_personalizado'])) {
        $tipo = trim($_POST['tipo_personalizado']);
    }

    $duracao = trim($_POST['duracao']);
    $modalidade = trim($_POST['modalidade']);
    $preco = $_POST['preco'];
    $descricao = trim($_POST['descricao']);
    $materiais_metodologia = trim($_POST['materiais_metodologia']);
    $conteudos = trim($_POST['conteudos']); 
    $publico_alvo = trim($_POST['publico_alvo']); 

    $imagem_atual = $_POST['imagem_atual'] ?? '';
    $nomeImagem = $imagem_atual;

    if (!empty($_FILES['imagem']['name'])) {
        $nomeImagem = time() . '_' . basename($_FILES['imagem']['name']);
        move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_dir . $nomeImagem);
    }

    if ($id_curso) {
        $sql = "UPDATE cursos SET titulo = ?, tipo = ?, duracao = ?, modalidade = ?, preco = ?, descricao = ?, materiais_metodologia = ?, conteudos = ?, publico_alvo = ?, imagem = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titulo, $tipo, $duracao, $modalidade, $preco, $descricao, $materiais_metodologia, $conteudos, $publico_alvo, $nomeImagem, $id_curso]);
        $mensagem_curso = "Curso atualizado com sucesso!";
    } else {
        if (!empty($nomeImagem)) {
            $sql = "INSERT INTO cursos (titulo, tipo, duracao, modalidade, preco, descricao, materiais_metodologia, conteudos, publico_alvo, imagem) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$titulo, $tipo, $duracao, $modalidade, $preco, $descricao, $materiais_metodologia, $conteudos, $publico_alvo, $nomeImagem]);
            $mensagem_curso = "Curso cadastrado com sucesso!";
        } else {
            $mensagem_curso = "Por favor, selecione uma imagem para o curso.";
        }
    }
}

// Excluir Curso
if (isset($_GET['excluir_curso'])) {
    $id_excluir = (int)$_GET['excluir_curso'];
    $stmt = $pdo->prepare("DELETE FROM cursos WHERE id = ?");
    $stmt->execute([$id_excluir]);
    header("Location: admin.php?aba=cursos");
    exit;
}

// Cabeçalho
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_cabecalho'])) {
    $stmt_cfg = $pdo->query("SELECT * FROM configuracoes WHERE id = 1");
    $config_atual = $stmt_cfg->fetch(PDO::FETCH_ASSOC);

    $link_home = $_POST['link_home'] ?? '';
    $link_alunos = $_POST['link_alunos'] ?? '';
    $link_blog = $_POST['link_blog'] ?? '';
    $link_contato = $_POST['link_contato'] ?? '';
    $link_quem_somos = $_POST['link_quem_somos'] ?? '';
    $link_area_aluno = $_POST['link_area_aluno'] ?? '';

    $logo1 = $config_atual['logo1'] ?? '';
    $logo2 = $config_atual['logo2'] ?? '';

    if (!empty($_FILES['logo1']['name'])) {
        $logo1 = time() . '_logo1_' . basename($_FILES['logo1']['name']);
        move_uploaded_file($_FILES['logo1']['tmp_name'], $upload_dir . $logo1);
    }

    if (!empty($_FILES['logo2']['name'])) {
        $logo2 = time() . '_logo2_' . basename($_FILES['logo2']['name']);
        move_uploaded_file($_FILES['logo2']['tmp_name'], $upload_dir . $logo2);
    }

    $sql = "UPDATE configuracoes SET logo1 = ?, logo2 = ?, link_home = ?, link_alunos = ?, link_blog = ?, link_contato = ?, link_quem_somos = ?, link_area_aluno = ? WHERE id = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$logo1, $logo2, $link_home, $link_alunos, $link_blog, $link_contato, $link_quem_somos, $link_area_aluno]);
    $mensagem_cabecalho = "Cabeçalho atualizado com sucesso!";
}

// Rodapé
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_rodape'])) {
    $stmt_rod = $pdo->query("SELECT * FROM rodape WHERE id = 1");
    $rodape_atual = $stmt_rod->fetch(PDO::FETCH_ASSOC);

    $telefone = $_POST['telefone'] ?? '';
    $whatsapp = $_POST['whatsapp'] ?? '';
    $email = $_POST['email'] ?? '';
    $link_facebook = $_POST['link_facebook'] ?? '';
    $link_instagram = $_POST['link_instagram'] ?? '';
    $texto_copyright = $_POST['texto_copyright'] ?? '';

    $logo_footer = $rodape_atual['logo_footer'] ?? '';
    $logo_faculdade = $rodape_atual['logo_faculdade'] ?? '';
    $qr_code = $rodape_atual['qr_code'] ?? '';

    if (!empty($_FILES['logo_footer']['name'])) {
        $logo_footer = time() . '_foot_' . basename($_FILES['logo_footer']['name']);
        move_uploaded_file($_FILES['logo_footer']['tmp_name'], $upload_dir . $logo_footer);
    }

    if (!empty($_FILES['logo_faculdade']['name'])) {
        $logo_faculdade = time() . '_fac_' . basename($_FILES['logo_faculdade']['name']);
        move_uploaded_file($_FILES['logo_faculdade']['tmp_name'], $upload_dir . $logo_faculdade);
    }

    if (!empty($_FILES['qr_code']['name'])) {
        $qr_code = time() . '_qr_' . basename($_FILES['qr_code']['name']);
        move_uploaded_file($_FILES['qr_code']['tmp_name'], $upload_dir . $qr_code);
    }

    $sql = "UPDATE rodape SET logo_footer = ?, logo_faculdade = ?, qr_code = ?, telefone = ?, whatsapp = ?, email = ?, link_facebook = ?, link_instagram = ?, texto_copyright = ? WHERE id = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$logo_footer, $logo_faculdade, $qr_code, $telefone, $whatsapp, $email, $link_facebook, $link_instagram, $texto_copyright]);
    $mensagem_rodape = "Rodapé atualizado com sucesso!";
}

// Sobre o Polo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_sobre'])) {
    $stmt_sb = $pdo->query("SELECT imagem FROM sobre_polo WHERE id = 1");
    $sobre_atual = $stmt_sb->fetch(PDO::FETCH_ASSOC);

    $titulo = $_POST['titulo'] ?? '';
    $p1 = $_POST['paragrafo_1'] ?? '';
    $p2 = $_POST['paragrafo_2'] ?? '';
    $p3 = $_POST['paragrafo_3'] ?? '';
    $imagem = $sobre_atual['imagem'] ?? '';

    if (!empty($_FILES['imagem']['name'])) {
        $imagem = time() . '_polo_' . basename($_FILES['imagem']['name']);
        move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_dir . $imagem);
    }

    $sql = "UPDATE sobre_polo SET titulo = ?, paragrafo_1 = ?, paragrafo_2 = ?, paragrafo_3 = ?, imagem = ? WHERE id = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$titulo, $p1, $p2, $p3, $imagem]);
    $mensagem_sobre = "Seção 'Sobre o Polo' atualizada com sucesso!";
}

// Vantagens / Diferenciais
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_vantagem'])) {
    $id = $_POST['id_vantagem'] ?? null;
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    
    $imagem = $_POST['imagem_atual'] ?? '';
    if (!empty($_FILES['imagem']['name'])) {
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $imagem = time() . '_vantagem_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_dir . $imagem);
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE vantagens SET titulo = ?, descricao = ?, imagem = ? WHERE id = ?");
        $stmt->execute([$titulo, $descricao, $imagem, $id]);
        $msg_vantagem = "Diferencial atualizado com sucesso!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO vantagens (titulo, descricao, imagem) VALUES (?, ?, ?)");
        $stmt->execute([$titulo, $descricao, $imagem]);
        $msg_vantagem = "Novo diferencial cadastrado com sucesso!";
    }
}

// Excluir Vantagem
if (isset($_GET['excluir_vantagem'])) {
    $id_excluir = (int)$_GET['excluir_vantagem'];
    $stmt = $pdo->prepare("DELETE FROM vantagens WHERE id = ?");
    $stmt->execute([$id_excluir]);
    header("Location: admin.php?aba=vantagens");
    exit;
}

// Excluir Lead
if (isset($_GET['excluir_lead'])) {
    $id_excluir = (int)$_GET['excluir_lead'];
    $stmt = $pdo->prepare("DELETE FROM leads WHERE id = ?");
    $stmt->execute([$id_excluir]);
    header("Location: admin.php?aba=leads");
    exit;
}

// --- CONSULTAS DADOS PARA O PAINEL ---
$stmt_config = $pdo->query("SELECT * FROM configuracoes WHERE id = 1");
$config = $stmt_config->fetch(PDO::FETCH_ASSOC);

$stmt_rodape = $pdo->query("SELECT * FROM rodape WHERE id = 1");
$rodape_dados = $stmt_rodape->fetch(PDO::FETCH_ASSOC);

$stmt_sobre = $pdo->query("SELECT * FROM sobre_polo WHERE id = 1");
$dados_sobre = $stmt_sobre->fetch(PDO::FETCH_ASSOC);

$stmt_banner = $pdo->query("SELECT * FROM banner_principal WHERE id = 1");
$dados_banner = $stmt_banner->fetch(PDO::FETCH_ASSOC);

$stmt_v = $pdo->query("SELECT * FROM vantagens ORDER BY id ASC");
$lista_vantagens = $stmt_v->fetchAll(PDO::FETCH_ASSOC);

$vantagem_edit = null;
if (isset($_GET['editar_vantagem'])) {
    $id_edit = (int)$_GET['editar_vantagem'];
    $stmt_e = $pdo->prepare("SELECT * FROM vantagens WHERE id = ?");
    $stmt_e->execute([$id_edit]);
    $vantagem_edit = $stmt_e->fetch(PDO::FETCH_ASSOC);
}

$stmt_cursos = $pdo->query("SELECT * FROM cursos ORDER BY id DESC");
$lista_cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);

$curso_edit = null;
if (isset($_GET['editar_curso'])) {
    $id_c_edit = (int)$_GET['editar_curso'];
    $stmt_c = $pdo->prepare("SELECT * FROM cursos WHERE id = ?");
    $stmt_c->execute([$id_c_edit]);
    $curso_edit = $stmt_c->fetch(PDO::FETCH_ASSOC);
}

// Consulta Lista de Professores
$lista_professores = [];
try {
    $stmt_prof = $pdo->query("SELECT * FROM professores ORDER BY id DESC");
    if ($stmt_prof) {
        $lista_professores = $stmt_prof->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $lista_professores = [];
}

$professor_edit = null;
if (isset($_GET['editar_professor'])) {
    $id_p_edit = (int)$_GET['editar_professor'];
    $stmt_p = $pdo->prepare("SELECT * FROM professores WHERE id = ?");
    $stmt_p->execute([$id_p_edit]);
    $professor_edit = $stmt_p->fetch(PDO::FETCH_ASSOC);
}

// Consulta Leads
$lista_leads = [];
try {
    $stmt_leads = $pdo->query("SELECT * FROM leads ORDER BY id DESC");
    if ($stmt_leads) {
        $lista_leads = $stmt_leads->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $lista_leads = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo - Fenatehd</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; display: flex; background: #f4f4f9; color: #333; }
        
        .sidebar { width: 250px; background: #111; color: #fff; min-height: 100vh; padding: 20px 0; }
        .sidebar h3 { text-align: center; color: #f57c00; margin-bottom: 30px; font-size: 1.2em; text-transform: uppercase; }
        .sidebar a { display: block; color: #ccc; padding: 15px 25px; text-decoration: none; border-left: 4px solid transparent; font-weight: bold; transition: all 0.2s; }
        .sidebar a:hover, .sidebar a.active { background: #222; color: #fff; border-left-color: #f57c00; }

        .main-content { flex: 1; padding: 40px; }
        .card { background: #fff; padding: 30px; border-radius: 8px; max-width: 900px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        h2 { margin-top: 0; color: #111; }
        label { font-weight: bold; display: block; margin-top: 15px; margin-bottom: 5px; font-size: 0.9em; }
        input[type="text"], input[type="number"], select, textarea, input[type="file"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 0.95em; }
        select { background: #fff; }
        button { background: #f57c00; color: #fff; border: none; padding: 12px 20px; border-radius: 4px; font-weight: bold; cursor: pointer; margin-top: 20px; width: 100%; font-size: 1em; }
        button:hover { background: #e65100; }
        .msg { background: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c8e6c9; }
        .preview-img { height: 60px; margin-top: 10px; background: #000; padding: 5px; border-radius: 4px; display: block; object-fit: contain; }
        
        .table-custom { width: 100%; border-collapse: collapse; margin-top: 15px; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .table-custom th { background: #222; color: #fff; text-align: left; padding: 12px; font-size: 0.9em; }
        .table-custom td { padding: 12px; border-bottom: 1px solid #eee; font-size: 0.9em; }
        .btn-edit { background: #2196F3; color: #fff; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85em; font-weight: bold; }
        .btn-delete { background: #f44336; color: #fff; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85em; font-weight: bold; }
        .badge-table { background: #e65100; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 0.8em; font-weight: bold; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h3>Painel ADMIN</h3>
        <a href="admin.php?aba=cursos" class="<?= $aba === 'cursos' ? 'active' : '' ?>">Gerenciar Cursos</a>
        <a href="admin.php?aba=professores" class="<?= $aba === 'professores' ? 'active' : '' ?>">Nossos Professores</a>
        <a href="admin.php?aba=vantagens" class="<?= $aba === 'vantagens' ? 'active' : '' ?>">Nossos Diferenciais</a>
        <a href="admin.php?aba=cabecalho" class="<?= $aba === 'cabecalho' ? 'active' : '' ?>">Editar Cabeçalho</a>
        <a href="admin.php?aba=rodape" class="<?= $aba === 'rodape' ? 'active' : '' ?>">Editar Rodapé</a>
        <a href="admin.php?aba=leads" class="<?= $aba === 'leads' ? 'active' : '' ?>">Ver Leads</a>
        <a href="admin.php?aba=sobre" class="<?= $aba === 'sobre' ? 'active' : '' ?>">Editar Sobre o Polo</a>
        <a href="admin.php?aba=banner" class="<?= $aba === 'banner' ? 'active' : '' ?>">Banner Principal</a>
        <a href="logout.php" style="margin-top: 30px;">Sair</a>
        <a href="../index.php" target="_blank" style="margin-top: 30px;">Ver Site ↗</a>
    </div>

    <div class="main-content">
        
        <!-- ABA: PROFESSORES -->
        <?php if ($aba === 'professores'): ?>
            <div class="card">
                <h2><?= $professor_edit ? 'Editar Professor' : 'Cadastrar Professor' ?></h2>
                <?php if ($msg_professor) echo "<p class='msg'>" . htmlspecialchars($msg_professor) . "</p>"; ?>

                <form action="admin.php?aba=professores" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="acao_professor" value="1">
                    <?php if ($professor_edit): ?>
                        <input type="hidden" name="id_professor" value="<?= $professor_edit['id'] ?>">
                        <input type="hidden" name="foto_atual" value="<?= htmlspecialchars($professor_edit['foto']) ?>">
                    <?php endif; ?>

                    <label>Nome do Professor / Título:</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($professor_edit['nome'] ?? '') ?>" required placeholder="Ex: Corpo Docente Qualificado ou Prof. Dr. João Silva">

                    <label>Titulação / Subtítulo (Badge):</label>
                    <input type="text" name="titulacao" value="<?= htmlspecialchars($professor_edit['titulacao'] ?? '') ?>" required placeholder="Ex: MESTRES E DOUTORES">

                    <label>Biografia / Descrição:</label>
                    <textarea name="biografia" rows="4" required placeholder="Ex: Nossa equipe é formada por profissionais renomados e atuantes no mercado."><?= htmlspecialchars($professor_edit['biografia'] ?? '') ?></textarea>

                    <label>Foto do Professor:</label>
                    <?php if (!empty($professor_edit['foto'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($professor_edit['foto']) ?>" class="preview-img">
                    <?php endif; ?>
                    <input type="file" name="foto" accept="image/*" <?= $professor_edit ? '' : 'required' ?>>

                    <button type="submit"><?= $professor_edit ? 'Salvar Alterações' : 'Cadastrar Professor' ?></button>
                    <?php if ($professor_edit): ?>
                        <a href="admin.php?aba=professores" style="display:inline-block; text-align:center; margin-top:10px; color:#666; text-decoration:none; width:100%;">Cancelar Edição</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card" style="max-width: 1000px;">
                <h3>Professores Cadastrados</h3>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nome</th>
                            <th>Titulação</th>
                            <th>Biografia</th>
                            <th style="text-align: center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($lista_professores)): ?>
                            <?php foreach ($lista_professores as $prof): ?>
                                <tr>
                                    <td>
                                        <img src="../uploads/<?= htmlspecialchars($prof['foto']) ?>" width="50" height="50" style="object-fit: cover; border-radius: 50%;">
                                    </td>
                                    <td><strong><?= htmlspecialchars($prof['nome']) ?></strong></td>
                                    <td><span class="badge-table"><?= htmlspecialchars($prof['titulacao']) ?></span></td>
                                    <td style="color: #666; font-size: 0.9em; max-width: 300px;"><?= htmlspecialchars($prof['biografia']) ?></td>
                                    <td style="text-align: center;">
                                        <a href="admin.php?aba=professores&editar_professor=<?= $prof['id'] ?>" class="btn-edit">Editar</a>
                                        <a href="admin.php?aba=professores&excluir_professor=<?= $prof['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir este professor?');" class="btn-delete">Excluir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #888; padding: 15px;">Nenhum professor cadastrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- DEMAIS ABAS PERMANECEM IGUAIS -->
        <?php if ($aba === 'cursos'): ?>
            <div class="card">
                <h2><?= $curso_edit ? 'Editar Curso' : 'Cadastrar Novo Curso' ?></h2>
                <?php if ($mensagem_curso) echo "<p class='msg'>" . htmlspecialchars($mensagem_curso) . "</p>"; ?>

                <form action="admin.php?aba=cursos" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="acao_curso" value="1">
                    <?php if ($curso_edit): ?>
                        <input type="hidden" name="id_curso" value="<?= $curso_edit['id'] ?>">
                        <input type="hidden" name="imagem_atual" value="<?= htmlspecialchars($curso_edit['imagem']) ?>">
                    <?php endif; ?>

                    <label>Título do Curso:</label>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($curso_edit['titulo'] ?? '') ?>" required>

                    <label>Categoria / Tipo de Curso:</label>
                    <?php 
                        $tipo_atual = $curso_edit['tipo'] ?? '';
                        $categorias_padrao = ['EXTENSÃO', 'CAPACITAÇÃO PROFISSIONAL', 'PÓS-GRADUAÇÃO', 'APERFEIÇOAMENTO', 'LIVRE'];
                        $is_outra = (!empty($tipo_atual) && !in_array($tipo_atual, $categorias_padrao));
                    ?>
                    <select name="tipo" id="select_tipo" onchange="toggleCategoriaOutro(this.value)" required>
                        <option value="">-- Selecione a Categoria --</option>
                        <option value="EXTENSÃO" <?= $tipo_atual === 'EXTENSÃO' ? 'selected' : '' ?>>EXTENSÃO</option>
                        <option value="CAPACITAÇÃO PROFISSIONAL" <?= $tipo_atual === 'CAPACITAÇÃO PROFISSIONAL' ? 'selected' : '' ?>>CAPACITAÇÃO PROFISSIONAL</option>
                        <option value="PÓS-GRADUAÇÃO" <?= $tipo_atual === 'PÓS-GRADUAÇÃO' ? 'selected' : '' ?>>PÓS-GRADUAÇÃO</option>
                        <option value="APERFEIÇOAMENTO" <?= $tipo_atual === 'APERFEIÇOAMENTO' ? 'selected' : '' ?>>APERFEIÇOAMENTO</option>
                        <option value="LIVRE" <?= $tipo_atual === 'LIVRE' ? 'selected' : '' ?>>LIVRE</option>
                        <option value="OUTRO" <?= $is_outra ? 'selected' : '' ?>>-- Outra Categoria --</option>
                    </select>

                    <div id="campo_categoria_outro" style="margin-top: 10px; display: <?= $is_outra ? 'block' : 'none' ?>;">
                        <input type="text" name="tipo_personalizado" value="<?= $is_outra ? htmlspecialchars($tipo_atual) : '' ?>" placeholder="Digite a nova categoria">
                    </div>

                    <label>Duração:</label>
                    <input type="text" name="duracao" value="<?= htmlspecialchars($curso_edit['duracao'] ?? '') ?>" required>

                    <label>Modalidade:</label>
                    <input type="text" name="modalidade" value="<?= htmlspecialchars($curso_edit['modalidade'] ?? 'EAD') ?>" required>

                    <label>Preço (R$):</label>
                    <input type="number" step="0.01" name="preco" value="<?= htmlspecialchars($curso_edit['preco'] ?? '') ?>" required>

                    <label>Sobre o Curso (Descrição):</label>
                    <textarea name="descricao" rows="4" required><?= htmlspecialchars($curso_edit['descricao'] ?? '') ?></textarea>

                    <label>Materiais e Metodologia:</label>
                    <textarea name="materiais_metodologia" rows="3"><?= htmlspecialchars($curso_edit['materiais_metodologia'] ?? '') ?></textarea>

                    <label>Conteúdo do Curso:</label>
                    <textarea name="conteudos" rows="3"><?= htmlspecialchars($curso_edit['conteudos'] ?? '') ?></textarea>

                    <label>Público-alvo:</label>
                    <textarea name="publico_alvo" rows="3"><?= htmlspecialchars($curso_edit['publico_alvo'] ?? '') ?></textarea>

                    <label>Imagem da Capa:</label>
                    <?php if (!empty($curso_edit['imagem'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($curso_edit['imagem']) ?>" class="preview-img">
                    <?php endif; ?>
                    <input type="file" name="imagem" accept="image/*" <?= $curso_edit ? '' : 'required' ?>>

                    <button type="submit"><?= $curso_edit ? 'Salvar Alterações' : 'Cadastrar Curso' ?></button>
                </form>
            </div>

            <div class="card" style="max-width: 1000px;">
                <h3>Cursos Cadastrados</h3>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Capa</th>
                            <th>Título</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th style="text-align: center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($lista_cursos)): ?>
                            <?php foreach ($lista_cursos as $c): ?>
                                <tr>
                                    <td><img src="../uploads/<?= htmlspecialchars($c['imagem']) ?>" width="50" height="50" style="object-fit: cover; border-radius: 6px;"></td>
                                    <td><strong><?= htmlspecialchars($c['titulo']) ?></strong></td>
                                    <td><span class="badge-table"><?= htmlspecialchars($c['tipo']) ?></span></td>
                                    <td>R$ <?= number_format($c['preco'], 2, ',', '.') ?></td>
                                    <td style="text-align: center;">
                                        <a href="admin.php?aba=cursos&editar_curso=<?= $c['id'] ?>" class="btn-edit">Editar</a>
                                        <a href="admin.php?aba=cursos&excluir_curso=<?= $c['id'] ?>" onclick="return confirm('Tem certeza?');" class="btn-delete">Excluir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <script>
                function toggleCategoriaOutro(valor) {
                    var divOutro = document.getElementById('campo_categoria_outro');
                    divOutro.style.display = (valor === 'OUTRO') ? 'block' : 'none';
                }
            </script>
        <?php endif; ?>

        <!-- DEMAIS ABAS (banner, vantagens, cabecalho, rodape, leads, sobre) MANTIDAS INTEGRALMENTE -->
        <?php if ($aba === 'banner'): ?>
            <div class="card">
                <h2>Editar Banner Principal (Hero)</h2>
                <?php if ($mensagem_banner) echo "<p class='msg'>" . htmlspecialchars($mensagem_banner) . "</p>"; ?>
                <form action="admin.php?aba=banner" method="POST">
                    <input type="hidden" name="acao_banner" value="1">
                    <label>Badge Topo:</label>
                    <input type="text" name="badge" value="<?= htmlspecialchars($dados_banner['badge'] ?? '') ?>" required>
                    <label>Título Principal:</label>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($dados_banner['titulo'] ?? '') ?>" required>
                    <label>Subtítulo:</label>
                    <input type="text" name="subtitulo" value="<?= htmlspecialchars($dados_banner['subtitulo'] ?? '') ?>" required>
                    <label>Descrição:</label>
                    <textarea name="descricao" rows="3" required><?= htmlspecialchars($dados_banner['descricao'] ?? '') ?></textarea>
                    <button type="submit">Salvar Alterações</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($aba === 'vantagens'): ?>
            <div class="card">
                <h2><?= $vantagem_edit ? 'Editar Card Diferencial' : 'Gerenciar Nossos Diferenciais' ?></h2>
                <?php if ($msg_vantagem) echo "<p class='msg'>" . htmlspecialchars($msg_vantagem) . "</p>"; ?>
                <form action="admin.php?aba=vantagens" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="acao_vantagem" value="1">
                    <?php if ($vantagem_edit): ?>
                        <input type="hidden" name="id_vantagem" value="<?= $vantagem_edit['id'] ?>">
                        <input type="hidden" name="imagem_atual" value="<?= htmlspecialchars($vantagem_edit['imagem']) ?>">
                    <?php endif; ?>
                    <label>Título do Card:</label>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($vantagem_edit['titulo'] ?? '') ?>" required>
                    <label>Descrição:</label>
                    <textarea name="descricao" rows="4" required><?= htmlspecialchars($vantagem_edit['descricao'] ?? '') ?></textarea>
                    <label>Ícone / Imagem:</label>
                    <?php if (!empty($vantagem_edit['imagem'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($vantagem_edit['imagem']) ?>" class="preview-img">
                    <?php endif; ?>
                    <input type="file" name="imagem" accept="image/*" <?= $vantagem_edit ? '' : 'required' ?>>
                    <button type="submit"><?= $vantagem_edit ? 'Salvar Alterações' : 'Cadastrar Card' ?></button>
                </form>
            </div>
            <div class="card" style="max-width: 1000px;">
                <h3>Cards Cadastrados</h3>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Ícone</th>
                            <th>Título</th>
                            <th>Descrição</th>
                            <th style="text-align: center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lista_vantagens as $v): ?>
                            <tr>
                                <td><img src="../uploads/<?= htmlspecialchars($v['imagem']) ?>" width="50" height="50" style="object-fit: contain; background:#000; padding:5px; border-radius: 6px;"></td>
                                <td><strong><?= htmlspecialchars($v['titulo']) ?></strong></td>
                                <td style="color: #666; font-size: 0.9em; max-width: 350px;"><?= htmlspecialchars($v['descricao']) ?></td>
                                <td style="text-align: center;">
                                    <a href="admin.php?aba=vantagens&editar_vantagem=<?= $v['id'] ?>" class="btn-edit">Editar</a>
                                    <a href="admin.php?aba=vantagens&excluir_vantagem=<?= $v['id'] ?>" onclick="return confirm('Tem certeza?');" class="btn-delete">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($aba === 'cabecalho'): ?>
            <div class="card">
                <h2>Configurações do Cabeçalho</h2>
                <?php if ($mensagem_cabecalho) echo "<p class='msg'>" . htmlspecialchars($mensagem_cabecalho) . "</p>"; ?>
                <form action="admin.php?aba=cabecalho" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="acao_cabecalho" value="1">
                    <label>Logo 1 (Fenatehd):</label>
                    <input type="file" name="logo1" accept="image/*">
                    <?php if(!empty($config['logo1'])): ?><img src="../uploads/<?= htmlspecialchars($config['logo1']) ?>" class="preview-img"><?php endif; ?>
                    <label>Logo 2 (LA Faculdades):</label>
                    <input type="file" name="logo2" accept="image/*">
                    <?php if(!empty($config['logo2'])): ?><img src="../uploads/<?= htmlspecialchars($config['logo2']) ?>" class="preview-img"><?php endif; ?>
                    <label>Link - Home:</label>
                    <input type="text" name="link_home" value="<?= htmlspecialchars($config['link_home'] ?? '') ?>">
                    <label>Link - Alunos:</label>
                    <input type="text" name="link_alunos" value="<?= htmlspecialchars($config['link_alunos'] ?? '') ?>">
                    <label>Link - Blog:</label>
                    <input type="text" name="link_blog" value="<?= htmlspecialchars($config['link_blog'] ?? '') ?>">
                    <label>Link - Contato:</label>
                    <input type="text" name="link_contato" value="<?= htmlspecialchars($config['link_contato'] ?? 'contato.php') ?>">
                    <label>Link - Quem Somos:</label>
                    <input type="text" name="link_quem_somos" value="<?= htmlspecialchars($config['link_quem_somos'] ?? 'quem-somos.php') ?>">
                    <label>Link - Área do Aluno:</label>
                    <input type="text" name="link_area_aluno" value="<?= htmlspecialchars($config['link_area_aluno'] ?? '') ?>">
                    <button type="submit">Salvar Alterações</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($aba === 'rodape'): ?>
            <div class="card">
                <h2>Configurações do Rodapé</h2>
                <?php if ($mensagem_rodape) echo "<p class='msg'>" . htmlspecialchars($mensagem_rodape) . "</p>"; ?>
                <form action="admin.php?aba=rodape" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="acao_rodape" value="1">
                    <label>Logo Principal:</label>
                    <input type="file" name="logo_footer" accept="image/*">
                    <?php if(!empty($rodape_dados['logo_footer'])): ?><img src="../uploads/<?= htmlspecialchars($rodape_dados['logo_footer']) ?>" class="preview-img"><?php endif; ?>
                    <label>Logo Secundária:</label>
                    <input type="file" name="logo_faculdade" accept="image/*">
                    <?php if(!empty($rodape_dados['logo_faculdade'])): ?><img src="../uploads/<?= htmlspecialchars($rodape_dados['logo_faculdade']) ?>" class="preview-img"><?php endif; ?>
                    <label>QR Code e-MEC:</label>
                    <input type="file" name="qr_code" accept="image/*">
                    <?php if(!empty($rodape_dados['qr_code'])): ?><img src="../uploads/<?= htmlspecialchars($rodape_dados['qr_code']) ?>" class="preview-img"><?php endif; ?>
                    <label>Telefone:</label>
                    <input type="text" name="telefone" value="<?= htmlspecialchars($rodape_dados['telefone'] ?? '') ?>">
                    <label>WhatsApp:</label>
                    <input type="text" name="whatsapp" value="<?= htmlspecialchars($rodape_dados['whatsapp'] ?? '') ?>">
                    <label>E-mail:</label>
                    <input type="text" name="email" value="<?= htmlspecialchars($rodape_dados['email'] ?? '') ?>">
                    <label>Facebook:</label>
                    <input type="text" name="link_facebook" value="<?= htmlspecialchars($rodape_dados['link_facebook'] ?? '') ?>">
                    <label>Instagram:</label>
                    <input type="text" name="link_instagram" value="<?= htmlspecialchars($rodape_dados['link_instagram'] ?? '') ?>">
                    <label>Copyright:</label>
                    <input type="text" name="texto_copyright" value="<?= htmlspecialchars($rodape_dados['texto_copyright'] ?? '') ?>">
                    <button type="submit">Salvar Alterações</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($aba === 'leads'): ?>
            <div class="card" style="max-width: 1000px;">
                <h2>Leads de Contato</h2>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Curso / Mensagem</th>
                            <th style="text-align: center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($lista_leads)): ?>
                            <?php foreach ($lista_leads as $lead): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($lead['criado_em'] ?? $lead['data_cadastro'] ?? 'now')) ?></td>
                                    <td><strong><?= htmlspecialchars($lead['nome'] ?? '') ?></strong></td>
                                    <td><?= htmlspecialchars($lead['email'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($lead['telefone'] ?? $lead['whatsapp'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($lead['curso'] ?? $lead['mensagem'] ?? '-') ?></td>
                                    <td style="text-align: center;">
                                        <a href="admin.php?aba=leads&excluir_lead=<?= $lead['id'] ?>" onclick="return confirm('Excluir este lead?');" class="btn-delete">Excluir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($aba === 'sobre'): ?>
            <div class="card">
                <h2>Editar Seção 'Sobre o Polo'</h2>
                <?php if ($mensagem_sobre) echo "<p class='msg'>" . htmlspecialchars($mensagem_sobre) . "</p>"; ?>
                <form action="admin.php?aba=sobre" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="acao_sobre" value="1">
                    <label>Título Principal:</label>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($dados_sobre['titulo'] ?? '') ?>" required>
                    <label>Parágrafo 1:</label>
                    <textarea name="paragrafo_1" rows="4" required><?= htmlspecialchars($dados_sobre['paragrafo_1'] ?? '') ?></textarea>
                    <label>Parágrafo 2:</label>
                    <textarea name="paragrafo_2" rows="4"><?= htmlspecialchars($dados_sobre['paragrafo_2'] ?? '') ?></textarea>
                    <label>Parágrafo 3:</label>
                    <textarea name="paragrafo_3" rows="4"><?= htmlspecialchars($dados_sobre['paragrafo_3'] ?? '') ?></textarea>
                    <label>Imagem Ilustrativa:</label>
                    <?php if (!empty($dados_sobre['imagem'])): ?><img src="../uploads/<?= htmlspecialchars($dados_sobre['imagem']) ?>" class="preview-img"><?php endif; ?>
                    <input type="file" name="imagem" accept="image/*">
                    <button type="submit">Salvar Alterações</button>
                </form>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>