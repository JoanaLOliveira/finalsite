<?php
session_start();

const ADMIN_USUARIO = 'admin';
const ADMIN_SENHA = 'admin123';

function usuario_admin_autenticado(): bool
{
    return isset($_SESSION['admin_autenticado']) && $_SESSION['admin_autenticado'] === true;
}

function exigir_login(): void
{
    if (!usuario_admin_autenticado()) {
        $pagina_atual = basename($_SERVER['PHP_SELF']);
        header('Location: login.php?redirect=' . urlencode($pagina_atual));
        exit;
    }
}
