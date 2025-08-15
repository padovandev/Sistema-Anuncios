<?php
session_start();
require_once __DIR__ . '/config.php';
if (empty($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="css/home.css">
    <title>Home | Sistema de Notícias</title>
</head>

<body>
    <header>
        <div>
            <strong>Home | Notícias</strong>
            <div class="muted">Olá, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</div>
        </div>
        <nav class="menu">
            <button onclick="viewList()">Lista de notícias</button>
            <button onclick="viewShow()">Exibir notícia</button>
            <button onclick="viewSync()">Sincronizar notícias</a></button>
            <button onclick="viewCreate()">Nova notícia</button>
            <button onclick="viewEdit()">Editar notícia</button>
            <button onclick="logout()">Sair do sistema</button>
        </nav>
    </header>

    <div class="container">
        <div class="card" id="content">Carregando…</div>
    </div>

    <script>
        const content = document.getElementById('content');

        async function apiGet(url) {
            const res = await fetch(url);
            return await res.json();
        }

        async function apiPost(url, data) {
            const fd = new FormData();
            for (const k in data) fd.append(k, data[k]);
            const res = await fetch(url, {
                method: 'POST',
                body: fd
            });
            return await res.json();
        }

        async function viewList() {
            content.innerHTML = 'Carregando lista…';
            const data = await apiGet('api/news.php?action=list');
            if (!data.ok) {
                content.textContent = data.error || 'Erro';
                return;
            }
            const items = data.data;
            if (!items.length) {
                content.innerHTML = '<em>Nenhuma notícia cadastrada.</em>';
                return;
            }
            const html = [`<ul class="list">`].concat(items.map(n => `
                <li>
                    <div>
                        <strong>#${n.id} — ${escapeHtml(n.title)}</strong><br>
                        <small class="muted">Criada em: ${n.created_at} | Atualizada em: ${n.updated_at}</small>
                    </div>
                    <div class="actions">
                        <button class="primary" onclick="viewShow(${n.id})">Ver</button>
                        <button onclick="viewEdit(${n.id})">Editar</button>
                        <button class="danger" onclick="removeNews(${n.id})">Excluir</button>
                    </div>
                </li>
    `)).concat(['</ul>']).join('');
            content.innerHTML = html;
        }

        async function viewShow(id) {
            if (!id) {
                // UI para escolher ID
                content.innerHTML = `
                    <div class="row">
                        <label>Informe o ID da notícia para exibir:</label>
                        <input id="showId" type="number" min="1" placeholder="Ex.: 1" />
                        <button class="primary" onclick="viewShow(parseInt(document.getElementById('showId').value))">Buscar</button>
                    </div>`;
                return;
            }
            content.innerHTML = 'Carregando…';
            const data = await apiGet(`api/news.php?action=get&id=${id}`);
            if (!data.ok) {
                content.textContent = data.error || 'Erro';
                return;
            }
            const n = data.data;
            content.innerHTML = `
                <h2>${escapeHtml(n.title)}</h2>
                <p>${escapeHtml(n.body).replaceAll('\n','<br>')}</p>
                <small class="muted">ID: ${n.id} | Criada: ${n.created_at} | Atualizada: ${n.updated_at}</small>
                <div class="actions" style="margin-top:12px">
                    <button onclick="viewEdit(${n.id})">Editar</button>
                    <button class="danger" onclick="removeNews(${n.id})">Excluir</button>
                </div>
                `;
        }

        async function viewCreate() {
            content.innerHTML = `
                <h2>Nova notícia</h2>
                <div class="row">
                    <input id="title" placeholder="Título" />
                    <textarea id="body" rows="6" placeholder="Conteúdo"></textarea>
                    <button class="primary" onclick="createNews()">Salvar</button>
                </div>
                `;
        }

        async function viewEdit(id) {
            if (!id) {
                content.innerHTML = `
                    <div class="row">
                        <label>Informe o ID da notícia para editar:</label>
                        <input id="editId" type="number" min="1" placeholder="Ex.: 1" />
                        <button class="primary" onclick="viewEdit(parseInt(document.getElementById('editId').value))">Carregar</button>
                    </div>`;
                return;
            }
            const data = await apiGet(`api/news.php?action=get&id=${id}`);
            if (!data.ok) {
                content.textContent = data.error || 'Erro';
                return;
            }
            const n = data.data;
            content.innerHTML = `
                <h2>Editar notícia #${n.id}</h2>
                <div class="row">
                    <input id="title" value="${escapeAttr(n.title)}" />
                    <textarea id="body" rows="6">${escapeHtml(n.body)}</textarea>
                    <div class="actions">
                        <button class="primary" onclick="updateNews(${n.id})">Salvar alterações</button>
                        <button onclick="viewShow(${n.id})">Cancelar</button>
                    </div>
                </div>
    `;
        }

        async function viewSync() {
            // Tela do Exercício 3: selecionar mês/ano e sincronizar NYTimes
            content.innerHTML = `
                <h2>Sincronizar NYTimes</h2>
                <div class="row">
                    <label>Ano
                        <input id="syncYear" type="number" min="1900" max="2100" value="${new Date().getFullYear()}" />
                    </label>

                    <label>Mês
                        <select id="syncMonth">${Array.from({length:12},(_,i)=> 
                            `<option value="${String(i+1).padStart(2,'0')}"> 
                                ${String(i + 1).padStart(2, '0')}
                            </option>`).join('')}</select >
                    </label>
                 
                    <button class="primary" onclick="doSync()"> Sincronizar </button>
                    <div id="syncMsg" class="muted"></div>
                </div> `;
        }

        // Coleta as informaçoes para criar as noticias
        async function createNews() {
            const title = document.getElementById('title').value.trim();
            const body = document.getElementById('body').value.trim();
            if (!title || !body) {
                alert('Preencha título e conteúdo');
                return;
            }
            const data = await apiPost('api/news.php', {
                action: 'create',
                title,
                body
            });
            if (data.ok) {
                viewShow(data.id);
            } else alert(data.error || 'Erro ao criar');
        }

        // Coleta as informaçoes para atualizar as noticias
        async function updateNews(id) {
            const title = document.getElementById('title').value.trim();
            const body = document.getElementById('body').value.trim();
            const data = await apiPost('api/news.php', {
                action: 'update',
                id,
                title,
                body
            });
            if (data.ok) {
                viewShow(id);
            } else alert(data.error || 'Erro ao atualizar');
        }

        // Coleta as informaçoes para excluir as noticias
        async function removeNews(id) {
            if (!confirm('Tem certeza que deseja excluir?')) return;
            const data = await apiPost('api/news.php', {
                action: 'delete',
                id
            });
            if (data.ok) {
                viewList();
            } else alert(data.error || 'Erro ao excluir');
        }

        async function doSync(){
            const year = document.getElementById('syncYear').value;
            const month = document.getElementById('syncMonth').value;
            const msg = document.getElementById('syncMsg');
            msg.textContent = 'Sincronizando…';
            const data = await apiPost('/api/news_sync.php', { year, month });
            if (data.ok) { msg.textContent = `Importadas ${data.imported} notícias.`; viewList(); }
            else { msg.textContent = data.error || 'Erro na sincronização'; }
        }

        async function syncNews() {
            content.innerHTML = 'Sincronizando…';
            const data = await apiPost('/api/news.php', {
                action: 'sync'
            });
            if (data.ok) {
                content.innerHTML = ` < p > Sincronização concluída.Novas criadas: < strong > $ {
                    data.created
                } < /strong></p > `;
                viewList();
            } else content.textContent = data.error || 'Erro na sincronização';
        }

        async function logout() {
            const data = await apiPost('auth.php', {
                action: 'logout'
            });
            if (data.ok) location.href = 'index.php';
        }

        // Helpers para escapar HTML
        function escapeHtml(s) {
            return String(s).replace(/[&<>"] /g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                ' ': ' '
            } [c]));
        }

        function escapeAttr(s) {
            return String(s).replace(/[&<>"]/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;'
            } [c]));
        }

        // Carrega a lista ao abrir
        viewList();
    </script>
</body>

</html>