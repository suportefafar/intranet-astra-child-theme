// Event Listeners
document.addEventListener("DOMContentLoaded", () => {
  // Export listed users
  const btn_export_users = document.querySelector("#btn_export_users");
  if (btn_export_users) {
    btn_export_users.addEventListener("click", () => {
      exportUsers();
    });
  }

  // Copy emails of listed users
  const btn_copy_emails = document.querySelector("#btn_copy_emails");
  if (btn_copy_emails) {
    btn_copy_emails.addEventListener("click", copyEmailsToClipboard);
  }

  // Attach listener on the search button
  document.querySelector("#search_button").addEventListener("click", () => {
    grid.forceRender(); // Re-render grid with new filters
  });

  // Attach listener to 'Enter' keyboard for search
  document.querySelectorAll("#filters_container input").forEach((el) =>
    el.addEventListener("keydown", function (e) {
      if (e.key === "Enter") {
        grid.forceRender(); // Re-render grid with new filters
      }
    })
  );
});

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
    { name: "Posição", formatter: positionColFormatter },
    { name: "Admissão", formatter: createdAtColFormatter },
    { name: "Ações", formatter: actionColFormatter },
  ],
  pagination: {
    limit: 10,
    server: {
      url: (prev, page, limit) => {
        const filters = getCurrentFilters(); // Get current filter values
        return `${prev}?limit=${limit}&offset=${
          page * limit
        }&${new URLSearchParams(filters)}`;
      },
    },
    summary: true,
  },
  server: {
    url: "/wp-json/intranet/v1/users",
    then: renderDataOnTable,
    total: (data) => data.count,
  },
  search: false,
  sort: true,
  resizable: true,
  autoWidth: true,
  language: ptBR,
}).render(document.getElementById("table-wrapper"));

/*
 * Get current filter values from DOM
 */
function getCurrentFilters() {
  return {
    keyword: document.querySelector("#input_keyword").value,
    place: document.querySelector("#input_place").value,
    status: document.querySelector("#select_bond_status").value,
    category: document.querySelector("#select_bond_categories").value,
    role: document.querySelector("#select_public_servant_role").value,
  };
}

function renderDataOnTable(data) {
  console.log(data);
  if (!data) {
    return [];
  }

  updateUsersCounter(data.count);

  return data.results.map((user) => {
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
      personal_phone,
      prevent_write,
    } = user;

    const nameData = JSON.stringify({
      display_name,
      workplace_extension,
      avatar_url,
      user_login,
      workplace_place,
      user_email,
      personal_phone,
    });

    const positionData = JSON.stringify({
      public_servant_bond_category,
      role,
    });

    const registeredData = JSON.stringify({ bond_status, user_registered });

    const actionData = JSON.stringify({ id: ID, prevent_write, user_login });

    return [nameData, positionData, registeredData, actionData];
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
    user_email,
    personal_phone,
  } = JSON.parse(current);

  return gridjs.html(`
    <div class="d-flex gap-2">
      <div>
        <img src="${avatar_url}" width="128" class="rounded-3">
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
                  workplace_place?.data ? workplace_place.data.number : ""
                }</span>
              </div>
            </div>
          </div>
        </div>
        
        <div class="d-flex gap-2">
          <span class="fs-6">
            <i class="bi bi-envelope"></i>
            <a href="mailto:${user_email}" target="_blank" title="Envie e-mail para ${user_email}">
              ${user_email}
            </a>
          </span>

          <span class="fs-6">
            <i class="bi bi-telephone"></i>
            <a href="tel:${personal_phone}" target="_blank" title="Ligar para o telefone ${personal_phone}">
              ${personal_phone}
            </a>
          </span>
        </div>
      </div>
    </div>`);
}

function positionColFormatter(current) {
  const { public_servant_bond_category, role } = JSON.parse(current);
  return gridjs.html(`
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

function createdAtColFormatter(current) {
  const { bond_status, user_registered } = JSON.parse(current);

  const type =
    {
      aposentado: "text-bg-warning",
      ativo: "text-bg-primary",
      removido: "text-bg-danger",
    }[bond_status.toLowerCase()] || "text-bg-info";

  return gridjs.html(`
      <div class="d-flex gap-2">
      ${
        user_registered
          ? `
        <i class="bi bi-calendar-event"></i>
        <span class="fs-6">${new Date(
          user_registered
        ).toLocaleDateString()}</span>`
          : ""
      }
        <span class="badge ${type}">${bond_status}</span>
      </div>
  `);
}

function actionColFormatter(current) {
  const { id, user_login, prevent_write } = JSON.parse(current);

  // Esse objeto é passado de forma global pelo page-usuario.php
  const { userLogin } = userLogged;

  return gridjs.html(`
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="/membros/${user_login}/profile/" target="_blank" title="Detalhes">
        <i class="bi bi-info-lg"></i>
      </a>
      <a class="btn btn-outline-secondary" href="/membros/${userLogin}/bp-messages/?bm-fast-start=1&to=${id}" target="_blank" title="Enviar mensagem">
        <i class="bi bi-send"></i>
      </a>
      ${
        !prevent_write
          ? `<a class="btn btn-outline-secondary" href="/wp-admin/user-edit.php?user_id=${id}" target="_blank" title="Editar">
        <i class="bi bi-pencil"></i>
      </a>`
          : ""
      }
    </div>
  `);
}

// Obter usuários para exportar e para copiar emails
async function getUsers() {
  const filters = getCurrentFilters(); // Get current filter values
  const url = `/wp-json/intranet/v1/users?${new URLSearchParams(filters)}`;

  try {
    const response = await axios.get(url);

    console.log(response.data);
    return response.data;
  } catch (error) {
    console.log(error.response.data.message);
    return [];
  }
}

/*
 * Função para exportar os usuários
 */
async function exportUsers() {
  showAlert("Por favor, aguarde...", "info", false, 0, true);

  const response = await getUsers();
  const users = response.results;

  if (!Array.isArray(users) || users.length === 0)
    return showAlert("Sem usuários para exportar!", "danger");

  // Gerar as linhas de CSV pelo objeto
  const csvLines = getCsvLinesFromItems(users);

  makeAndDownloadCSV(csvLines);

  showAlert("Usuários exportados com sucesso!", "success", true);
}

function criarLinhaCSV(arr) {
  return Object.keys(arr)
    .map((attr) => arr[attr])
    .join(",");
}

function getAttrDisplayName(attr) {
  return (
    {
      ID: "ID",
      user_login: "Usuário",
      user_pass: "Senha",
      user_nicename: "Apelido",
      user_email: "Email",
      user_url: "URL",
      user_registered: "Registrado em",
      user_activation_key: "Chave de ativação",
      user_status: "Status",
      display_name: "Nome",
      avatar_url: "Foto",
      personal_phone: "Telefone",
      personal_birthday: "Aniversário",
      personal_cpf: "CPF",
      personal_ufmg_registration: "Inscrição UFMG",
      personal_siape: "SIAPE",
      address_cep_code: "CEP",
      address_uf: "UF",
      address_city: "Cidade",
      address_neighborhood: "Bairro",
      address_public_place: "Logradouro",
      address_number: "Número",
      address_complement: "Complemento",
      public_servant_bond_type: "Tipo de vínculo",
      public_servant_bond_category: "Categoria de vínculo",
      public_servant_bond_position: "Cargo de vínculo",
      public_servant_bond_class: "Classe de vínculo",
      public_servant_bond_level: "Nível de vínculo",
      role: "Setor de vínculo",
      bond_status: "Status de vínculo",
      workplace_place: "Sala de trabalho",
      workplace_extension: "Ramal de trabalho",
    }[attr] || "Desconhecido"
  );
}

function getValuesFromAttr(attr, item) {
  if (attr === "role") {
    return item.role.name ?? "--";
  } else if (attr === "workplace_place") {
    return item.workplace_place.data ? item.workplace_place.data.number : "--";
  } else {
    return item[attr] || "--";
  }
}

function getCsvLinesFromItems(items) {
  if (!Array.isArray(items) || items.length === 0) return "";

  const raw_attributes = Object.keys(items[0]);

  const attributes = raw_attributes.map((attr) => getAttrDisplayName(attr));

  const csvLines = [criarLinhaCSV(attributes)];

  for (const item of items) {
    const obj = Object.keys(item).map((attr) => {
      return getValuesFromAttr(attr, item);
    });

    csvLines.push(criarLinhaCSV(obj));
  }

  return csvLines.join("\n");
}

function makeAndDownloadCSV(csvContent, filenamePrefix = "usuarios") {
  const blob = new Blob([csvContent], { type: "text/csv" });

  const url = URL.createObjectURL(blob);

  const anchor = document.createElement("a");

  anchor.href = url;

  anchor.download = `${filenamePrefix}_${Date.now()}.csv`;

  document.body.appendChild(anchor); // Ensure the anchor is in the DOM before triggering the click

  anchor.click();

  document.body.removeChild(anchor); // Clean up after download

  setTimeout(() => URL.revokeObjectURL(url), 1000); // Give more time to ensure revocation doesn't interfere
}

/*
 * Função para copiar os emails
 */
async function copyEmailsToClipboard() {
  console.log("Copiando...");
  showAlert("Por favor, aguarde...", "info", false, 0, true);

  const response = await getUsers();
  const users = response.results;

  if (!Array.isArray(users) || users.length === 0) {
    console.log("Sem usuários para copiar.");
    return showAlert("Sem usuários para copiar.", "danger");
  }

  if (!navigator.clipboard) {
    console.log("Funcionalidade não encontrada em seu navegador.");
    showAlert("Funcionalidade não encontrada em seu navegador.", "danger");
    return;
  }

  const emails = users.map((user) => user.user_email);

  // console.log("Copiando " + emails.length + " emails...");

  navigator.clipboard.writeText(emails).then(
    function () {
      showAlert("Copiado(s) com sucesso!", "success", true);
    },
    function (err) {
      console.error("Async: Could not copy text: ", err);
      showAlert("Falha ao copiar!", "danger");
    }
  );
}

/*
 * Atualizador do elemento que mostra a quantidade de usuários
 */
function updateUsersCounter(quantity) {
  const usersCounter = document.querySelector("#users_counter");
  if (usersCounter) {
    usersCounter.innerHTML = quantity;
  }
}

/*
 * Helper Functions
 */
function capitalizeFirstLetter(value) {
  if (!value) return "";
  return value.charAt(0).toUpperCase() + value.slice(1).toLowerCase();
}
