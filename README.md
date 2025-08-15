# 📂 Sistema de Gerenciamento de Noticias

Um sistema web simples para gerenciar notícias, com interface administrativa para criar, editar, excluir e sincronizar conteúdos com uma fonte externa (NYTimes).
O projeto é construído em PHP no backend e HTML/JS/CSS no frontend, utilizando AJAX para comunicação assíncrona.

---

# 📡 Como rodar em servidor local (TESTE)

- 1. Extraia os Arquivos para a pasta que desejar.
- 2. Após extrair, logo na pasta de origem dos arquivos, abra o terminal.
- 3. Execute o comando: 
        . php -S localhost:8000; 
- 4. Após inciar o servidor local do PHP, entre no link: http://localhost:8000/ 

- OBS: A porta utilizada não pode ser uma porta que ja está em uso. 

---

## ⚙️ Funcionalidades

- Autenticação de Usuário 
- Login e logout seguro via sessões PHP. 
- Proteção de rotas: apenas usuários autenticados podem acessar o painel administrativo. 
- Gerenciamento de Notícias. 
- Listar todas as notícias cadastradas. 
- Exibir notícia individual por ID. 
- Criar novas notícias com título e conteúdo. 
- Editar notícias existentes.  
- Excluir notícias.  
- Sincronização Externa.
- Tela de sincronização com ano e mês selecionáveis. 
- Busca e importa notícias de uma fonte externa (ex.: NYTimes API). 
- Mensagem de retorno indicando quantas notícias foram importadas. 
- Interface Responsiva 
- Layout simples e limpo em HTML/CSS. 
- Uso de JavaScript para navegação dinâmica entre telas sem recarregar a página. 

---

## 🧱 Tecnologias Utilizadas

- 💻 Backend: PHP 8+
- 🎨 Frontend: HTML5, CSS3, JavaScript (vanilla)
- 🔗 Comunicação: AJAX via fetch()
- 📂 Banco de Dados: SQLite (configuração em config.php)
- 📨 Autenticação: Sessões PHP

---

## 💻 Uso da Interface

- Lista de notícias: viewList()
- Exibir notícia: viewShow(id)
- Criar notícia: viewCreate() → createNews()
- Editar notícia: viewEdit(id) → updateNews(id)
- Excluir notícia: removeNews(id)
- Sincronizar notícias externas: viewSync() → doSync()
- Todas as operações são feitas via AJAX, sem recarregar a página.

---

## 👀 Observações

- ✅ Endpoint de sincronização deve estar configurado com a API externa correta.
- ✅ Configurações utilizadas no php.ini: 
-     . extension=curl;
-     . extension=pdo_sqlite;
-     . extension=sqlite3; 
-     . curl.cainfo = "C:\php8.4\extras\ssl\cacert.pem"; 
-     . openssl.cafile = "C:\php\extras\ssl\cacert.pem"; 
- ✅ Layout simples e responsivo para facilitar uso administrativo.
- ✅ Mensagens de erro e sucesso são exibidas dinamicamente.

