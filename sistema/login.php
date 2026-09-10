<?php
require_once __DIR__ . '/auth.php';

if (usuario_admin_autenticado()) {
    header('Location: admin.php');
    exit;
}

$erro = '';
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'admin.php';
$paginas_permitidas = ['admin.php', 'admin_cabecalho.php'];
if (!in_array($redirect, $paginas_permitidas, true)) {
    $redirect = 'admin.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (hash_equals(ADMIN_USUARIO, $usuario) && hash_equals(ADMIN_SENHA, $senha)) {
        session_regenerate_id(true);
        $_SESSION['admin_autenticado'] = true;
        $_SESSION['admin_usuario'] = $usuario;
        header('Location: ' . $redirect);
        exit;
    }

    $erro = 'Usuário ou senha inválidos.';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Administrativo</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f4f4f9; color: #333; }
        .login-card { width: min(100% - 32px, 420px); background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); }
        h1 { margin: 0 0 8px; color: #111; font-size: 1.7rem; }
        .subtitle { margin: 0 0 24px; color: #666; }
        label { display: block; margin: 16px 0 6px; font-weight: bold; }
        input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 1rem; }
        button { width: 100%; margin-top: 24px; padding: 12px; border: 0; border-radius: 4px; background: #f57c00; color: #fff; font-size: 1rem; font-weight: bold; cursor: pointer; }
        button:hover { background: #e65100; }
        .erro { padding: 12px; border: 1px solid #ffcdd2; border-radius: 4px; background: #ffebee; color: #c62828; }
    </style>
</head>
<body>
    <main class="login-card">
        <h1>Painel Administrativo</h1>
        <p class="subtitle">Entre para editar o conteúdo do site.</p>
        <?php if ($erro): ?>
            <p class="erro"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') ?>">
            <label for="usuario">Usuário</label>
            <input id="usuario" type="text" name="usuario" required autofocus>
            <label for="senha">Senha</label>
            <input id="senha" type="password" name="senha" required>
            <button type="submit">Entrar</button>
        </form>
    </main>
</body>
</html>
