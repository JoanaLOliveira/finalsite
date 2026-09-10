<?php
require_once __DIR__ . '/auth.php';
exigir_login();

// Inclui o arquivo conexao.php localizado na raiz do projeto
require_once __DIR__ . '/../conexao.php';

// Busca dados atuais do cabeçalho
$stmt = $pdo->query("SELECT * FROM configuracoes WHERE id = 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $link_home = $_POST['link_home'] ?? '';
    $link_alunos = $_POST['link_alunos'] ?? '';
    $link_blog = $_POST['link_blog'] ?? '';
    $link_contato = $_POST['link_contato'] ?? '';
    $link_quem_somos = $_POST['link_quem_somos'] ?? '';
    $link_area_aluno = $_POST['link_area_aluno'] ?? '';

    $logo1 = $config['logo1'] ?? '';
    $logo2 = $config['logo2'] ?? '';

    // Diretório de uploads na raiz do projeto
    $upload_dir = __DIR__ . '/../uploads/';

    // Processamento da Logo 1 (Fenatehd)
    if (!empty($_FILES['logo1']['name'])) {
        $logo1 = time() . '_logo1_' . basename($_FILES['logo1']['name']);
        move_uploaded_file($_FILES['logo1']['tmp_name'], $upload_dir . $logo1);
    }

    // Processamento da Logo 2 (LA Faculdades)
    if (!empty($_FILES['logo2']['name'])) {
        $logo2 = time() . '_logo2_' . basename($_FILES['logo2']['name']);
        move_uploaded_file($_FILES['logo2']['tmp_name'], $upload_dir . $logo2);
    }

    $sql = "UPDATE configuracoes SET 
            logo1 = ?, logo2 = ?, link_home = ?, link_alunos = ?, 
            link_blog = ?, link_contato = ?, link_quem_somos = ?, link_area_aluno = ? 
            WHERE id = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$logo1, $logo2, $link_home, $link_alunos, $link_blog, $link_contato, $link_quem_somos, $link_area_aluno]);

    $mensagem = "Cabeçalho atualizado com sucesso!";
    
    // Atualiza os dados exibidos na tela
    $stmt = $pdo->query("SELECT * FROM configuracoes WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel - Editar Cabeçalho</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; display: flex; background: #f4f4f9; }
        
        /* Menu Lateral */
        .sidebar { width: 250px; background: #111; color: #fff; min-height: 100vh; padding: 20px 0; }
        .sidebar h3 { text-align: center; color: #f57c00; margin-bottom: 30px; }
        .sidebar a { display: block; color: #ccc; padding: 15px 25px; text-decoration: none; border-left: 4px solid transparent; }
        .sidebar a:hover, .sidebar a.active { background: #222; color: #fff; border-left-color: #f57c00; }

        /* Conteúdo Principal */
        .main-content { flex: 1; padding: 40px; }
        .card { background: #fff; padding: 30px; border-radius: 8px; max-width: 800px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        label { font-weight: bold; display: block; margin-top: 15px; margin-bottom: 5px; font-size: 0.9em; }
        input[type="text"], input[type="file"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #f57c00; color: #fff; border: none; padding: 12px 20px; border-radius: 4px; font-weight: bold; cursor: pointer; margin-top: 25px; width: 100%; }
        button:hover { background: #e65100; }
        .msg { background: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        .preview-img { height: 40px; margin-top: 5px; background: #000; padding: 5px; border-radius: 4px; }
    </style>
</head>
<body>

    <!-- Sidebar Lateral -->
    <div class="sidebar">
        <h3>Painel ADMIN</h3>
        <a href="admin.php">Cadastrar Cursos</a>
        <a href="admin_cabecalho.php" class="active">Editar Cabeçalho</a>
        <a href="logout.php">Sair</a>
        <a href="../index.php" target="_blank">Ver Site ↗</a>
    </div>

    <!-- ÁREA DE EDICÃO -->
    <div class="main-content">
        <div class="card">
            <h2>Configurações do Cabeçalho</h2>
            <?php if (isset($mensagem)) echo "<p class='msg'>" . htmlspecialchars($mensagem) . "</p>"; ?>

            <form action="admin_cabecalho.php" method="POST" enctype="multipart/form-data">
                
                <label>Logo 1 (Fenatehd):</label>
                <input type="file" name="logo1" accept="image/*">
                <?php if(!empty($config['logo1'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($config['logo1']) ?>" class="preview-img" alt="Logo 1">
                <?php endif; ?>

                <label>Logo 2 (LA Faculdades):</label>
                <input type="file" name="logo2" accept="image/*">
                <?php if(!empty($config['logo2'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($config['logo2']) ?>" class="preview-img" alt="Logo 2">
                <?php endif; ?>

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

                <label>Link - Botão "Área do Aluno":</label>
                <input type="text" name="link_area_aluno" value="<?= htmlspecialchars($config['link_area_aluno'] ?? '') ?>">

                <button type="submit">Salvar Alterações</button>
            </form>
        </div>
    </div>

</body>
</html>