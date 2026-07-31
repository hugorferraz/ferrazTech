<?php
$paginaAtual = basename($_SERVER['PHP_SELF']);

$tituloTela = "Painel";
if ($paginaAtual == 'index.php') {
    $tituloTela = "Início";
} elseif ($paginaAtual == 'clientes.php' || $paginaAtual == 'detalhes_cliente.php') {
    $tituloTela = "Clientes";
} elseif ($paginaAtual == 'agendamentos.php') {
    $tituloTela = "Agendamentos";
}
?>

<nav class="navbar">
    <div class="nav-brand">
        <h2>FerrazTech - <?php echo $tituloTela; ?></h2>
    </div>
    <ul class="nav-links">
        <li><a href="index.php" <?php echo ($paginaAtual == 'index.php') ? 'style="color: #3498db;"' : ''; ?>>Dashboard</a></li>
        <li><a href="clientes.php" <?php echo ($paginaAtual == 'clientes.php' || $paginaAtual == 'detalhes_cliente.php') ? 'style="color: #3498db;"' : ''; ?>>Clientes</a></li>
        <li><a href="agendamentos.php" <?php echo ($paginaAtual == 'agendamentos.php') ? 'style="color: #3498db;"' : ''; ?>>Agendamentos</a></li>
    </ul>
</nav>
<hr style="margin-bottom: 20px; border: 0; border-top: 1px solid #ddd;">