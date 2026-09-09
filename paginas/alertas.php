<!DOCTYPE html>

<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    ```
    <title>Alertas - Valistoque</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/alertas.css">
    ```

</head>

<body>

    ```
    <!-- Menu -->
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

    <main>

        <!-- Área dos alertas -->
        <div id="secao-alertas">

            <div class="alertas-page">

                <div class="secao-header">
                    <h1>Central de Alertas</h1>
                </div>

                <div class="alertas-grid">

                    <!-- Configuração dos alertas -->
                    <div class="alertas-card">

                        <h2>Configuração dos avisos</h2>

                        <p>
                            Configure quando o sistema deve mostrar um aviso
                            sobre produtos próximos da validade ou com pouco estoque.
                        </p>

                        <form id="form-config-alertas" onsubmit="salvarConfiguracoesAlertas(event)">

                            <label for="dias-alerta-validade">
                                Dias antes da validade para avisar
                            </label>

                            <input type="number" id="dias-alerta-validade" min="1" value="30" required>

                            <label for="caixas-alerta-estoque">
                                Quantidade mínima de caixas
                            </label>

                            <input type="number" id="caixas-alerta-estoque" min="1" value="10" required>

                            <label for="intervalo-alerta">
                                Intervalo para verificar os alertas
                            </label>

                            <select id="intervalo-alerta" required>
                                <option value="5">5 minutos</option>
                                <option value="10">10 minutos</option>
                                <option value="15">15 minutos</option>
                                <option value="30">30 minutos</option>
                                <option value="60">60 minutos</option>
                            </select>

                            <div class="texto-auxiliar">
                                As configurações ficam salvas no navegador.
                            </div>

                            <button type="submit">
                                Salvar configuração
                            </button>

                        </form>

                    </div>


                    <!-- Resumo dos alertas -->
                    <div>

                        <div class="alertas-resumo">

                            <div class="resumo-box">
                                <span>Total de alertas</span>
                                <strong id="resumo-total-alertas">0</strong>
                            </div>

                            <div class="resumo-box alerta-critico">
                                <span>Alertas críticos</span>
                                <strong id="resumo-alertas-criticos">0</strong>
                            </div>

                            <div class="resumo-box alerta-aviso">
                                <span>Alertas de aviso</span>
                                <strong id="resumo-alertas-aviso">0</strong>
                            </div>

                        </div>


                        <!-- Histórico dos alertas -->
                        <div class="historico-alertas">

                            <div class="historico-header">

                                <h2>Histórico de alertas</h2>

                                <div class="filtros-alerta">

                                    <button type="button" class="filtro-btn ativo" data-filtro="todos"
                                        onclick="filtrarAlertas('todos')">
                                        Todos
                                    </button>

                                    <button type="button" class="filtro-btn" data-filtro="validade"
                                        onclick="filtrarAlertas('validade')">
                                        Validade
                                    </button>

                                    <button type="button" class="filtro-btn" data-filtro="estoque"
                                        onclick="filtrarAlertas('estoque')">
                                        Estoque
                                    </button>

                                    <button type="button" class="filtro-btn" data-filtro="critico"
                                        onclick="filtrarAlertas('critico')">
                                        Críticos
                                    </button>

                                </div>

                            </div>

                            <div class="lista-alertas-completa" id="lista-alertas-completa"></div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </main>

    <script type="module" src="../js/alertas.js"></script>
    ```

</body>

</html>