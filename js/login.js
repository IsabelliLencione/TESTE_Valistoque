const formLogin = document.getElementById("login-form");

      formLogin.addEventListener("submit", (event) => {
        event.preventDefault();

        const email = document
          .getElementById("email")
          .value.trim()
          .toLowerCase();
        const senha = document.getElementById("senha").value.trim();
        const perfil = document.getElementById("perfil").value;

        // Mapeamento das contas e seus destinos
        const usuarioDemo = {
          administrador: {
            email: "admin@valistoque.com",
            senha: "admin123",
            destino: "interiorAdm.html#relatorio",
            nome: "Administrador",
          },
          // Trecho a ser ajustado no login.html
          funcionario: {
            email: "funcionario@valistoque.com",
            senha: "func123",
            destino: "interiorFunc.html#alertas", // <--- Mudar para carregar direto no interiorFunc com a aba de Alertas
            nome: "Funcionário",
          },
        };

        if (!email || !senha) {
          alert("Preencha email e senha.");
          return;
        }

        const contaSelecionada = usuarioDemo[perfil];

        // Valida se o email e a senha correspondem exatamente ao perfil selecionado
        if (
          email === contaSelecionada.email &&
          senha === contaSelecionada.senha
        ) {
          // Salva os dados do usuário autenticado no localStorage
          const dadosUsuario = {
            email: email,
            perfil: perfil,
            nome: contaSelecionada.nome,
          };

          localStorage.setItem(
            "valistoqueUsuario",
            JSON.stringify(dadosUsuario),
          );
          localStorage.setItem("perfilUsuario", JSON.stringify(dadosUsuario));

          // Redireciona para o destino (no caso do funcionário, tela_alerta.html)
          window.location.href = contaSelecionada.destino;
        } else {
          alert("E-mail ou senha incorretos para o perfil selecionado.");
        }
      });
   