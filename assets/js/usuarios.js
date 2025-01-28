import { Grid, html } from "https://unpkg.com/gridjs?module";

/*
 * Event Listeners
 */
let USERS = [];

// Export listed users
document.querySelector("#btn_export_users").addEventListener("click", () => {
  console.log("Exportando usuários!");
});

// Copy emails of listed users
document
  .querySelector("#btn_copy_emails")
  .addEventListener("click", copyEmailsToClipboard);

// Attach listeners to filter inputs and selects
document
  .querySelectorAll("#filters_container input, #filters_container select")
  .forEach((el) => el.addEventListener("input", filterUsers));

// Initialize fetch on DOMContentLoaded
document.addEventListener("DOMContentLoaded", initialFetch);

/*
 * Grid.js Setup
 */
const ptBR = {
  search: { placeholder: "Digite uma palavra-chave..." },
  sort: {
    sortAsc: "Coluna em ordem crescente",
    sortDesc: "Coluna em ordem decrescente",
  },
  pagination: {
    previous: "Anterior",
    next: "Próxima",
    navigate: (page, total) => `Página ${page} de ${total}`,
    page: (page) => `Página ${page}`,
    showing: "Mostrando",
    of: "de",
    to: "até",
    results: "resultados",
  },
  loading: "Carregando...",
  noRecordsFound: "Nenhum registro encontrado",
  error: "Ocorreu um erro ao buscar os dados",
};

const grid = new gridjs.Grid({
  columns: [
    { name: "Nome", formatter: nameColFormatter },
    { name: "Email", formatter: emailColFormatter },
    { name: "Posição", formatter: positionColFormatter },
    { name: "Status", formatter: statusColFormatter },
    { name: "Adimito em", formatter: createdAtColFormatter },
    { name: "Ações", formatter: actionColFormatter },
  ],
  data: [],
  pagination: {
    limit: 20,
    summary: true,
  },
  search: false,
  sort: true,
  resizable: true,
  language: ptBR,
}).render(document.getElementById("table-wrapper"));

/*
 * API Functions
 */

async function getUsers() {
  try {
    const response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/users"
    );
    return JSON.parse(response.data);
  } catch (error) {
    console.error(
      "Failed to fetch users:",
      error.response?.data?.message || error
    );
    return [];
  }
}

async function initialFetch() {
  const users = await getUsers();
  prepareToRenderDataOnTable(users);
}

/*
 * Filtering and Rendering
 */
async function filterUsers() {
  const filters = {
    user_name: document.querySelector("#input_user_name").value,
    bond_status: document.querySelector("#select_bond_status").value,
    bond_categories: document.querySelector("#select_bond_categories").value,
    public_servant_role: document.querySelector("#select_public_servant_role")
      .value,
  };

  console.log("Filters:", filters);

  const users = await getUsers();

  const filtered_users = users.filter((user) => {
    return (
      user.display_name.toLowerCase().indexOf(filters.user_name.toLowerCase()) >
        -1 &&
      (filters.bond_status === "" ||
        filters.bond_status === user.bond_status) &&
      (filters.bond_categories === "" ||
        filters.bond_categories === user.public_servant_bond_category) &&
      (filters.public_servant_role === "" ||
        filters.public_servant_role === user.role.slug)
    );
  });

  console.log(filtered_users);

  prepareToRenderDataOnTable(filtered_users);
}

function prepareToRenderDataOnTable(data = []) {
  if (!Array.isArray(data)) {
    console.error("Invalid data format. Expected an array.");
    data = [];
  }

  USERS = data;

  grid.updateConfig({ data: renderDataOnTable(data) }).forceRender();
}

function renderDataOnTable(users) {
  if (!Array.isArray(users)) {
    console.error("Expected an array of users, got:", users);
    return [];
  }

  return users
    .filter(({ ID }) => ID !== "1") // Exclude user with ID 1
    .map((user) => {
      const {
        ID,
        display_name,
        user_email,
        workplace_extension,
        avatar_url,
        user_login,
        public_servant_bond_category,
        role,
        bond_status,
        user_registered,
        workplace_place,
        prevent_write,
        prevent_exec,
      } = user;

      const nameData = JSON.stringify({
        display_name,
        workplace_extension,
        avatar_url,
        user_login,
        workplace_place,
      });

      const positionData = JSON.stringify({
        public_servant_bond_category,
        role,
      });

      const permissions = `${prevent_write ? "1" : "0"}${
        prevent_exec ? "1" : "0"
      }`;

      const actionData = JSON.stringify({ id: ID, permissions, user_login });

      return [
        nameData,
        user_email,
        positionData,
        bond_status,
        user_registered,
        actionData,
      ];
    });
}

/*
 * Formatters
 */
function nameColFormatter(current) {
  const {
    display_name,
    workplace_extension,
    avatar_url,
    user_login,
    workplace_place,
  } = JSON.parse(current);

  return html(`
    <div class="d-flex gap-2">
      <div>
        <img src="${avatar_url}" width="64" class="rounded-circle">
      </div>
      <div class="w-100 d-flex gap-2 flex-column justify-content-center">
        <strong class="mb-0 fs-5">
          <a href="/membros/${user_login}/" target="blank" title="${display_name}" class="text-decoration-none">
            ${display_name}
          </a>
        </strong>
        <div class="d-flex justify-content-between">
          <div>
            <div class="d-flex gap-2">
              <div>
                <i class="bi bi-telephone fs-6 fw-light"></i>
                <span class="fs-6">${workplace_extension ?? "--"}</span>
              </div>
              <div>
                <i class="bi bi-geo-alt"></i>
                <span>${
                  workplace_place.data ? workplace_place.data.number : ""
                }</span>
              </div>
          </div>
        </div>
      </div>
    </div>`);
}

function positionColFormatter(current) {
  const { public_servant_bond_category, role } = JSON.parse(current);
  return html(`
    <div class="d-flex gap-2 flex-column">
      <div class="d-flex gap-1">
        <i class="bi bi-bookmark"></i>
        <span class="fs-6">${public_servant_bond_category}</span>
      </div>
      <div class="d-flex gap-1">
        <i class="bi bi-person-gear fs-6"></i>
        <span class="fs-6">${capitalizeFirstLetter(role.name)}</span>
      </div>
    </div>
    `);
}

function emailColFormatter(current) {
  return html(`
    <div class="d-flex gap-2">
      <i class="bi bi-envelope"></i>
      <span class="fs-6">${current}</span>
    </div>
    `);
}

function createdAtColFormatter(current) {
  return html(`
      <div class="d-flex gap-2">
        <i class="bi bi-calendar-event"></i>
        <span class="fs-6">${new Date(current).toLocaleDateString()}</span>
      </div>
  `);
}

function statusColFormatter(current) {
  const type =
    {
      emprestado: "text-bg-warning",
      ativado: "text-bg-primary",
      desativado: "text-bg-danger",
      quebrado: "text-bg-danger",
      desaparecido: "text-bg-danger",
    }[current.toLowerCase()] || "text-bg-info";

  return html(`<span class="badge ${type}">${current}</span>`);
}

function actionColFormatter(current) {
  const { id, user_login } = JSON.parse(current);
  return html(`
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="/membros/${user_login}/profile/" target="_blank" title="Detalhes">
        <i class="bi bi-info-lg"></i>
      </a>
      <a class="btn btn-outline-secondary" href="/membros/rootadmfafar_intranet/bp-messages/#/conversation/${id}" target="_blank" title="Enviar mensagem">
        <i class="bi bi-send"></i>
      </a>
      <a class="btn btn-outline-secondary" href="/wp-admin/user-edit.php?user_id=${id}" target="_blank" title="Editar">
        <i class="bi bi-pencil"></i>
      </a>
    </div>
  `);
}

/*
 * Função para exportar os usuários
 */

function downloadCSV(csv) {
  //TRANSFORMAR EM .CSV E DOWNLOAD
  const atual = new Date(Date.now());

  const csvBlob = new Blob([csv], { type: "text/csv" });
  const blobUrl = URL.createObjectURL(csvBlob);
  const anchorElement = document.createElement("a");

  anchorElement.href = blobUrl;
  anchorElement.download = "USUARIOS_" + atual.getTime() + ".csv";
  anchorElement.click();

  setTimeout(() => {
    URL.revokeObjectURL(blobUrl);
  }, 500);
}

function exibirProblemas(problemas) {
  if (problemas.length > 0) {
    let content =
      "Ops, tivemos algum(s) problemas. Mas não se preocupe, seu arquivo foi gerado. Segue o que aconteceu:<br/>";

    problemas.forEach((element) => {
      let erro = "Erro não identificado para usuario: " + element.usuario.nome;
      if (element.cod_erro == 001) {
        erro =
          "Endereço não encontrado ou inválido para o usuario: " +
          element.usuario.nome;
      } else if (element.cod_erro == 002) {
        erro = "Propriedade não encontrada no usuário: " + element.usuario.nome;
      }

      content += erro + "<br>";
    });

    $.alert({
      title:
        "<span class='text-primary'><i class='bi bi-exclamation-triangle-fill'></i></span> Atenção",
      content,
      buttons: {
        voltar: {
          text: "Ok!",
          action: function () {},
        },
      },
    });
  }
}

function exportarParaCSV(atributos) {
  /*
  códigos problemas:
    001: endereco não cadastrado;
    */
  let problemas = [];
  let usuarios_completos = [];
  let usuarios_filtrados = [];
  let enderecos_filtrados = [];
  let promises_usuarios = [];
  let promises_enderecos = [];

  let ids_filtrados = $(".td-nome")
    .toArray()
    .filter((element) => $(element).is(":visible"));

  ids_filtrados.forEach((element) => {
    promises_usuarios.push(
      Promise.resolve(
        obterUsuarioPorID($(element).find("input[type=hidden]").val())
      )
    );
  });

  ids_filtrados.forEach((element) => {
    promises_enderecos.push(
      Promise.resolve(
        obterUsuarioEnderecoPorIdUsuario(
          $(element).find("input[type=hidden]").val()
        )
      )
    );
  });

  Promise.all(promises_usuarios)
    .then((res) => {
      usuarios_filtrados = res;

      return Promise.all(promises_enderecos);
    })
    .then((res) => {
      enderecos_filtrados = res;

      for (const usuario of usuarios_filtrados) {
        let ENDERECO_VALIDO = false;
        let USUARIO_VALIDO = false;

        if (usuario) {
          USUARIO_VALIDO = true;
          for (const endereco of enderecos_filtrados) {
            if (usuario && endereco && usuario.id == endereco.idusuario) {
              usuarios_completos.push({ ...usuario, ...endereco });
              ENDERECO_VALIDO = true;
            }
          }
        }

        if (!ENDERECO_VALIDO) {
          problemas.push({
            cod_erro: 001,
            usuario: {
              id: usuario.id ? usuario.id : "--",
              nome: usuario.nome ? usuario.nome : "--",
            },
          });
        }

        if (!USUARIO_VALIDO) {
          problemas.push({
            cod_erro: 002,
            usuario: {
              id: usuario.id ? usuario.id : "--",
              nome: usuario.nome ? usuario.nome : "--",
            },
          });
        }
      }

      usuarios_filtrados = filtrarAtributos(atributos, usuarios_completos);

      let csv =
        criarLinhaCSV(Object.keys(usuarios_filtrados[0])).toUpperCase() + "\n";

      for (const usuario of usuarios_filtrados) {
        let linha = criarLinhaCSV(usuario);
        csv += linha + "\n";
      }

      downloadCSV(csv);

      exibirProblemas(problemas);
    });
}

/*
 * Função para copiar os emails
 */
function copyEmailsToClipboard() {
  console.log(USERS);

  showAlert("Por favor, aguarde...", "info");

  if (!navigator.clipboard) {
    showAlert(
      "Sem navigator.clipboard. Por favor, informe ao setor de Informática.",
      "danger"
    );
    return;
  }

  const emails = USERS.map((user) => user.user_email);

  navigator.clipboard.writeText(emails).then(
    function () {
      showAlert("Email(s) copiado(s) com sucesso!");
    },
    function (err) {
      console.error("Async: Could not copy text: ", err);
      showAlert("Não foi possível copiar os emails!");
    }
  );
}

/*
 * Helper Functions
 */

function capitalizeFirstLetter(value) {
  return value.charAt(0).toUpperCase() + value.slice(1).toLowerCase();
}
