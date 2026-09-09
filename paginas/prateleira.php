<!DOCTYPE html>

<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    ```
    <title>Prateleiras - Valistoque</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/prateleira.css">
    ```

</head>

<body>

    ```
    <!-- Menu lateral -->
    <nav class="nav">

        <div class="header-nav">
            Valistoque
        </div>

        <ul>

            <li>
                <a href="relatorio.html">Relatório</a>
            </li>

            <li>
                <a href="produtos.php">Cadastro Produtos</a>
            </li>

            <li>
                <a href="usuarios.php">Cadastro Usuários</a>
            </li>

            <li>
                <a href="prateleira.html">Prateleiras</a>
            </li>

            <li>
                <a href="estoque.php">Estoque Central</a>
            </li>

            <li>
                <a href="alertas.html">Alertas</a>
            </li>

            <li>
                <a href="ListaUsuarios.html">Usuários</a>
            </li>

            <li style="margin-top: auto; border-top: 1px solid #34495e;">
                <a href="perfil.html">Perfil</a>
            </li>

        </ul>

    </nav>


    <!-- Conteúdo principal -->
    <main>

        <!-- Área das prateleiras -->
        <div id="secao-prateleira">

            <div class="secao-header">

                <h1>Prateleiras</h1>

                <button class="btn-adicionar btn-mais"
                    onclick="document.getElementById('cadastrarPrateleira').showModal()" title="Adicionar Prateleira">
                    +
                </button>

            </div>


            <!-- Busca -->
            <form action="/buscar" method="get" class="form-busca-prateleira" onsubmit="return false;">

                <div>

                    <label for="prateleiraBusca">
                        Buscar prateleira
                    </label>

                    <input type="text" id="prateleiraBusca" placeholder="Buscar prateleira, produto ou lote...">

                </div>

            </form>


            <!-- Lista de prateleiras -->
            <div class="prateleiras-container">
                <!-- As prateleiras serão carregadas pelo JavaScript -->
            </div>


            <!-- Janela para cadastrar uma prateleira -->
            <dialog id="cadastrarPrateleira">

                <div class="btnEtitulo">

                    <h3>
                        Adicionar Prateleira
                    </h3>

                    <button onclick="cadastrarPrateleira.close()" class="btn-fechar" aria-label="Fechar">
                        &times;
                    </button>

                </div>


                <div class="modal-footer">

                    <form id="form-prateleira" onsubmit="return false;">

                        <!-- Escolher prateleira -->
                        <div>

                            <label for="num-prateleira">
                                Selecione a prateleira
                            </label>

                            <select id="num-prateleira" required></select>

                        </div>


                        <!-- Escolher lote -->
                        <div>

                            <label for="lote-prat">
                                Lote do produto
                            </label>

                            <select id="lote-prat" required></select>

                        </div>


                        <!-- Quantidade -->
                        <div>

                            <label for="caixas-prat">
                                Quantidade de caixas
                            </label>

                            <input type="number" id="caixas-prat" placeholder="Ex: 5" min="1" required>

                        </div>


                        <!-- Cadastrar -->
                        <button id="closeModalBtn" class="btn-close">
                            Cadastrar
                        </button>

                    </form>

                </div>

            </dialog>

        </div>

    </main>


    <!-- JavaScript -->
    <script type="module" src="../js/prateleira.js"></script>
    ```

</body>

</html>