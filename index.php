<?php
require_once __DIR__ . '/config.php';
if (!empty($_SESSION['user_id'])) {
    header('Location: /home.php');
    exit;
}
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="css/index.css">
    <title>Login | Sistema de Notícias</title>
</head>

<body>
    <div class="card">
        <h1>Sistema de Notícias</h1>
        <p>Faça login ou crie sua conta</p>
        <div class="tabs">
            <button id="tabLogin" class="active">Entrar</button>
            <button id="tabRegister">Cadastrar</button>
        </div>

        <form id="formLogin">
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="password" placeholder="Senha" required>
            <button class="primary" type="submit">Entrar</button>
            <div class="msg" id="msgLogin"></div>
        </form>

        <form id="formRegister" style="display:none">
            <input type="text" name="name" placeholder="Nome" required>
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="password" placeholder="Senha" required>
            <button class="primary" type="submit">Cadastrar</button>
            <div class="msg" id="msgRegister"></div>
        </form>

        <p style="font-size: 12px;">Criado por: Fabricio Padovan | Sistema Teste NÃO utilizado oficialmente.</p>
    </div>

    <script>
        const tabLogin = document.getElementById('tabLogin');
        const tabRegister = document.getElementById('tabRegister');
        const formLogin = document.getElementById('formLogin');
        const formRegister = document.getElementById('formRegister');
        const msgLogin = document.getElementById('msgLogin');
        const msgRegister = document.getElementById('msgRegister');

        tabLogin.onclick = () => {
            tabLogin.classList.add('active');
            tabRegister.classList.remove('active');
            formLogin.style.display = 'grid';
            formRegister.style.display = 'none';
        };
        tabRegister.onclick = () => {
            tabRegister.classList.add('active');
            tabLogin.classList.remove('active');
            formRegister.style.display = 'grid';
            formLogin.style.display = 'none';
        };

        formLogin.addEventListener('submit', async (e) => {
            e.preventDefault();
            msgLogin.textContent = '';
            const fd = new FormData(formLogin);
            fd.append('action', 'login');
            const res = await fetch('/auth.php', {
                method: 'POST',
                body: fd
            });
            const data = await res.json();
            if (data.ok) location.href = '/home.php';
            else msgLogin.textContent = data.error || 'Erro ao entrar';
        });

        formRegister.addEventListener('submit', async (e) => {
            e.preventDefault();
            msgRegister.textContent = '';
            const fd = new FormData(formRegister);
            fd.append('action', 'register');
            const res = await fetch('/auth.php', {
                method: 'POST',
                body: fd
            });
            const data = await res.json();
            if (data.ok) location.href = '/home.php';
            else msgRegister.textContent = data.error || 'Erro no cadastro';
        });
    </script>
</body>

</html>