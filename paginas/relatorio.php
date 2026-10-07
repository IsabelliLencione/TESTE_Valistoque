<!DOCTYPE html>

<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

   
    <title>Relatório - Valistoque</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/relatorio.css">
   

</head>

<body>

  
    <!-- Menu lateral -->
<nav class="nav"> 
  <div class="header-nav">Valistoque</div> 
  <ul> 
    <!-- Relatório -->
    <li>
      <a href="relatorio.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
        Relatório
      </a>
    </li> 

    <!-- Cadastro Produtos -->
    <li>
      <a href="produtos.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-8 11c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3z"/></svg>
        Cadastro Produtos
      </a>
    </li> 

    <!-- Cadastro Usuários -->
    <li>
      <a href="usuarios.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 12 4 12zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        Cadastro Usuários
      </a>
    </li> 

    <!-- Prateleiras -->
    <li>
      <a href="prateleira.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M4 11h5V5H4v6zm0 8h5v-6H4v6zm6 0h5v-6h-5v6zm6 0h5v-6h-5v6zm-6-8h5V5h-5v6zm6-6v6h5V5h-5z"/></svg>
        Prateleiras
      </a>
    </li> 

    <!-- Estoque Central -->
    <li>
      <a href="estoque.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-5 14H4v-4h11v4zm0-5H4V9h11v4zm5 5h-4V9h4v9z"/></svg>
        Estoque Central
      </a>
    </li> 

    <!-- Alertas -->
    <li>
      <a href="alertas.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
        Alertas
      </a>
    </li> 

    <!-- Usuários -->
    <li>
      <a class="profile-item" href="ListaUsuarios.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        Usuários
      </a>
    </li>
    

    <li style="margin-top: auto; border-top: 1px solid #34495e;">
      <a href="principal.html">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
      <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
      </svg>
      Sair
      </a>
  </li>
  </ul> 
</nav>


    <!-- Conteúdo principal -->
    <main>

        <div id="secao-relatorio">

            <div class="relatorio-container">

                <!-- Escolha do mês e ano -->
                <div class="content-header">

                    <div class="month-selector">

                        <span class="seta-mes" onclick="mudarMes(-1)">
                            ❮
                        </span>

                        <span id="txt-mes">
                            Março
                        </span>

                        <span class="seta-mes" onclick="mudarMes(1)">
                            ❯
                        </span>

                    </div>

                    <h1 id="txt-ano">
                        2026
                    </h1>

                </div>


                <!-- Resumo das movimentações -->
                <div class="cards-grid">

                    <!-- Saídas do estoque -->
                    <div class="card">

                        <h3>
                            Saída - Estoque central
                        </h3>

                        <div class="card-list-scroll" id="saida-estoque"></div>

                    </div>


                    <!-- Entradas do estoque -->
                    <div class="card">

                        <h3>
                            Entrada - Estoque central
                        </h3>

                        <div class="card-list-scroll" id="entrada-estoque"></div>

                    </div>


                    <!-- Saídas das prateleiras -->
                    <div class="card">

                        <h3>
                            Saída - Prateleira
                        </h3>

                        <div class="card-list-scroll" id="saida-prateleira"></div>

                    </div>


                    <!-- Entradas das prateleiras -->
                    <div class="card">

                        <h3>
                            Entrada - Prateleira
                        </h3>

                        <div class="card-list-scroll" id="entrada-prateleira"></div>

                    </div>

                </div>


                <!-- Alertas -->
                <div class="alert-card">

                    <h3>
                        Alertas emitidos
                    </h3>

                    <div class="alert-content">

                        <div class="alert-list-scroll" id="lista-alertas"></div>

                    </div>

                </div>

            </div>

        </div>

    </main>


    <!-- JavaScript do relatório -->
    <script type="module" src="../js/relatorio.js"></script>
 

</body>

</html>