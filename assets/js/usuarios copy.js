import { Grid, html, h, PluginPosition } from "https://unpkg.com/gridjs?module";

let USERS = [];
/*
 * LISTENER'S
 */

/*
 * Listener para o botão que copia os emails dos usuários listados
 */
document.querySelector("#btn_copy_emails").addEventListener("click", () => {
  console.log(USERS);
});

/*
 * Listener para o botão que exporta os usuários listados
 */
document.querySelector("#btn_export_users").addEventListener("click", () => {
  console.log(USERS);
});

/*
 * Listeners para qualquer mudança
 * que aconteça em inputs ou selects de filtro
 */
document
  .querySelectorAll("#filters_container input")
  .forEach((el) => el.addEventListener("input", filterUsers));
document
  .querySelectorAll("#filters_container select")
  .forEach((el) => el.addEventListener("input", filterUsers));

/*
 * Aguarda até que a DOM seja carregada para inserir os eventos no calendário
 */
document.addEventListener("DOMContentLoaded", () => {
  initialFetch();
});

/*
 * Setup objetos do Grid.JS
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
    navigate: function (e, r) {
      return "Página " + e + " de " + r;
    },
    page: function (e) {
      return "Página " + e;
    },
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

async function getUsers() {
  let response;

  try {
    response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/users"
    );
  } catch (error) {
    console.log(error.response.data.message);
    return [];
  }

  return JSON.parse(response.data);
}

async function initialFetch() {
  const users = await getUsers();

  prepareToRenderDataOnTable(users);
}

async function filterUsers() {
  const input_user_name = document.querySelector("#input_user_name");
  const select_bond_status = document.querySelector("#select_bond_status");
  const select_bond_categories = document.querySelector(
    "#select_bond_categories"
  );
  const select_public_servant_role = document.querySelector(
    "#select_public_servant_role"
  );

  const user_name = input_user_name.value;
  const bond_status = select_bond_status.value;
  const bond_categories = select_bond_categories.value;
  const public_servant_role = select_public_servant_role.value;

  console.log({
    user_name,
    bond_status,
    bond_categories,
    public_servant_role,
  });

  const users = await getUsers();

  console.log(users);

  prepareToRenderDataOnTable(users);
}

function prepareToRenderDataOnTable(data = []) {
  try {
    // Ensure data is an array and properly formatted
    if (!Array.isArray(data)) {
      console.error("Invalid data format. Expected an array.");
      data = [];
    }

    console.log("Processed Data:", data);

    // Update the Grid.js configuration
    grid
      .updateConfig({
        data: renderDataOnTable(data), // Ensure this returns valid Grid.js data
      })
      .forceRender();
  } catch (error) {
    console.error("Error rendering data on table:", error);
  }
}

function renderDataOnTable(users) {
  console.log("Input Users:", users); // Log the incoming users for debugging

  if (!Array.isArray(users)) {
    console.error("Expected an array of users, got:", users);
    return [];
  }

  let table_arr = [];
  for (const submission of users) {
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
    } = submission;

    // Skip user with ID 1
    if (ID === "1") continue;

    const name_column_data = JSON.stringify({
      display_name,
      workplace_extension,
      avatar_url,
      user_login,
      workplace_place,
    });

    const position_column_data = JSON.stringify({
      public_servant_bond_category,
      role,
    });

    const prevent_write = submission.prevent_write ? "1" : "0";
    const prevent_exec = submission.prevent_exec ? "1" : "0";
    const permissions = prevent_write + prevent_exec;

    const action_column_data = JSON.stringify({
      id: ID,
      permissions,
      user_login,
    });

    table_arr.push([
      name_column_data,
      user_email,
      position_column_data,
      bond_status,
      user_registered,
      action_column_data,
    ]);
  }

  return table_arr;
}

function nameColFormatter(current) {
  const {
    display_name,
    workplace_extension,
    avatar_url,
    user_login,
    workplace_place,
  } = JSON.parse(current);

  const html_content = `
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
    </div>`;

  return html(html_content);
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
  let type = "text-bg-info";
  const current_lower = current ? current.toLowerCase() : "";

  if (current_lower === "emprestado") type = "text-bg-warning";
  else if (current_lower === "ativado") type = "text-bg-primary";
  else if (
    current_lower === "desativado" ||
    current_lower === "quebrado" ||
    current_lower === "desaparecido"
  )
    type = "text-bg-danger";

  return html(`<span class="badge ${type}">${current}</span>`);
}

function actionColFormatter(current, row) {
  const { id, permissions, user_login } = JSON.parse(current);

  const prevent_write = parseInt(permissions.split("")[0]);

  const html_content = `
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="/membros/${user_login}/profile/" target="blank" title="Detalhes">
        <i class="bi bi-info-lg"></i>
      </a>
      <a class="btn btn-outline-secondary" href="/membros/rootadmfafar_intranet/bp-messages/#/conversation/${id}" target="blank" title="Enviar mensagem">
        <i class="bi bi-send"></i>
      </a>
      <a class="btn btn-outline-secondary" href="/wp-admin/user-edit.php?user_id=${id}&wp_http_referer=%2Fwp-admin%2Fusers.php" target="blank" title="Editar">
        <i class="bi bi-pencil"></i>
      </a>
    </div>`;

  return html(html_content);
}

function capitalizeFirstLetter(val) {
  return (
    String(val.toLowerCase()).charAt(0).toUpperCase() +
    String(val.toLowerCase()).slice(1)
  );
}
