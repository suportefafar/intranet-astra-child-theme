console.log(USERS_APOIO_LOGISTICO);
console.log(USERS_TI);
console.log(TYPES_APOIO_LOGISTICO);
console.log(TYPES_TI);

/*
 * LISTENER'S
 */
/*
 * Adiciona um evento de clique à DOM,
 * e despara se o elemento que recebeu o clique tem
 * a classe 'btn-loan-equipament' ou é filho de um elemento
 * com essa classe
 */
document.addEventListener("click", (event) => {
  const deleteButton = event.target.closest(".btn-delete-submission");

  if (deleteButton) {
    const id = deleteButton.dataset.id;
    confirmDelete(id);
  }
});

document
  .querySelector("#departament_assigned_to")
  .addEventListener("change", (e) => {
    const departament_name = e.target.value;

    console.log(departament_name);

    changeFiltersByDepartament(departament_name);
  });

document.getElementById("search-form").addEventListener("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(e.target);
  const data = {};

  // Get all name/value pairs
  formData.forEach((value, name) => {
    data[name] = value;
  });

  console.log(data);

  const queryString = Array.from(formData.entries())
    .filter(([name, value]) => value !== "") // Exclude empty values
    .map(
      ([name, value]) =>
        `${encodeURIComponent(name)}=${encodeURIComponent(value)}`
    )
    .join("&");

  console.log(queryString);

  document.querySelector("#table-wrapper").classList.remove("d-none");

  grid
    .updateConfig({
      pagination: {
        limit: 10,
        server: {
          url: (prev, page, limit) => {
            let junction = prev.indexOf("?") > 0 ? "&" : "?";
            console.log(`${prev}${junction}limit=${limit}&offset=${page + 1}`);
            return `${prev}${junction}limit=${limit}&offset=${page + 1}`;
          },
        },
        summary: true,
      },
      server: {
        url: "/wp-json/intranet/v1/submissions/service_tickets/?" + queryString,
        then: renderDataOnTable,
        total: (data) => data.count,
      },
    })
    .forceRender();

  window.location = "/filtro-avancado-de-ordem-de-servico/#table-wrapper";
});

/*
 * CHARTS RENDER
 */

/*
 * TABLE RENDER
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
    {
      name: "Número",
      formatter: numberColFormatter,
    },
    {
      name: "Descrição",
      formatter: descColFormatter,
    },
    { name: "Responsável", formatter: applicantColFormatter },
    { name: "Departamento", formatter: deparmentColFormatter },
    {
      name: "Status",
      formatter: statusColFormatter,
    },
    {
      name: "Criado/Atualizado",
      formatter: createdAtColFormatter,
    },
  ],
  data: [],
  sort: true,
  resizable: true,
  autoWidth: true,
  language: ptBR,
}).render(document.getElementById("table-wrapper"));

/*
 * Para adicionar listeners aos spans com descrição, e abrir o Popover
 */
const observer = new MutationObserver((mutationsList) => {
  for (const mutation of mutationsList) {
    if (mutation.type === "childList") {
      // Reinitialize popovers when new elements are added
      const popoverTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="popover"]')
      );
      popoverTriggerList.map((popoverTriggerEl) => {
        return new bootstrap.Popover(popoverTriggerEl);
      });
    }
  }
});

// Start observing the table container for changes
const tableContainer = document.getElementById("table-wrapper");
observer.observe(tableContainer, { childList: true, subtree: true });

function renderDataOnTable(data) {
  // Early return if data is invalid or empty
  if (!data || !Array.isArray(data.results)) {
    return [];
  }

  // Map through the results and transform each submission
  return data.results.map((submission) => {
    const { id, data, updated_at, created_at, relationships } = submission;
    const {
      assigned_to,
      status,
      type,
      number,
      user_report,
      departament_assigned_to,
    } = data;

    const number_column_data = JSON.stringify({
      id,
      number,
    });

    const desc_column_data = JSON.stringify({
      type,
      user_report,
    });

    const date_column_data = JSON.stringify({
      updated_at,
      created_at,
    });

    // submission_data.departament_assigned_to.role_display_name
    return [
      number_column_data,
      desc_column_data,
      relationships.applicant,
      relationships.departament_assigned_to.role_display_name,
      status,
      date_column_data,
    ];
  });
}

function numberColFormatter(current) {
  const { id, number } = JSON.parse(current);

  const html_content = `
      <div class="d-flex gap-2">
        <a href="/visualizar-ordem-de-servico/?id=${id}" title="Detalhes" target="_blank">
          ${number}
        </a>
      </div>`;

  return gridjs.html(html_content);
}

function descColFormatter(current) {
  const { type, user_report } = JSON.parse(current);

  const MAX_CHAR_DESC = 80;

  const short_user_report =
    user_report && user_report.length > MAX_CHAR_DESC
      ? user_report.slice(0, MAX_CHAR_DESC) + "..."
      : user_report;

  return gridjs.html(`
      <div class="d-flex flex-column gap-1">
        <div>
          <span>
            ${type}
          </span>
        </div>
  
        <a 
            class="d-inline-block text-secondary fafar-cursor-pointer" 
            tabindex="0" 
            data-bs-toggle="popover" 
            data-bs-trigger="focus" 
            data-bs-title="Relato" 
            data-bs-content="${user_report.replaceAll('"', "'")}">
          ${short_user_report.replaceAll('"', "'")}
          </a>
      </div>
      `);
}

function applicantColFormatter(current) {
  return gridjs.html(`
        <div>
          <i class="bi bi-person"></i>
          ${
            current
              ? `<a href="/membros/${current.user_login}/" target="blank" title="${current.display_name}">${current.display_name}</a>`
              : "--"
          }
        </div>
    `);
}

function deparmentColFormatter(current) {
  return gridjs.html(`
      <div>
        <i class="bi bi-bookmark"></i>
        <span>${current ?? "--"}</span>
      </div>
    `);
}

function statusColFormatter(current) {
  let type = "text-bg-info";
  const current_lower = current.toLowerCase();

  if (current_lower === "nova") type = "text-bg-success";
  else if (current_lower === "aguardando") type = "text-bg-warning";
  else if (current_lower === "em andamento") type = "text-bg-primary";
  else if (current_lower === "finalizada") type = "text-bg-secondary";
  else if (current_lower === "cancelada") type = "text-bg-danger";

  return gridjs.html(`<span class="badge ${type}">${current}</span>`);
}

function createdAtColFormatter(current) {
  const { updated_at, created_at } = JSON.parse(current);

  const formatted_created_at = formatDateTime(created_at);

  const formatted_updated_at = formatDateTime(updated_at);

  const how_long_created_at = getDateAsHowLongFormatted(created_at);

  const how_long_updated_at = getDateAsHowLongFormatted(updated_at);

  return gridjs.html(`
      <div class="d-flex flex-column gap-1">
        <div>
          <i class="bi bi-clock"></i>
          <a href="#" class="text-decoration-none" title="Criado em ${formatted_created_at}">${how_long_created_at}</a>
        </div>
  
        <div>
          <i class="bi bi-arrow-clockwise"></i>
          <a href="#" class="text-decoration-none" title="Atualizado em ${formatted_updated_at}">${how_long_updated_at}</a>
        </div>
      </div>
    `);
}

function changeFiltersByDepartament(departament) {
  const type_select = document.querySelector("#type");
  const assigned_to_select = document.querySelector("#assigned_to");

  type_select.disabled = false;
  assigned_to_select.disabled = false;

  if (departament === "apoio_logistico_e_operacional") {
    insertOptionsOnSelect(
      "#type",
      TYPES_APOIO_LOGISTICO,
      TYPES_APOIO_LOGISTICO
    );
    insertOptionsOnSelect(
      "#assigned_to",
      USERS_APOIO_LOGISTICO.map((u) => u.display_name),
      USERS_APOIO_LOGISTICO.map((u) => u.ID)
    );
  } else if (departament === "tecnologia_da_informacao_e_suporte") {
    insertOptionsOnSelect("#type", TYPES_TI, TYPES_TI);
    insertOptionsOnSelect(
      "#assigned_to",
      USERS_TI.map((u) => u.display_name),
      USERS_TI.map((u) => u.ID)
    );
  } else {
    type_select.disabled = true;
    assigned_to_select.disabled = true;

    type_select.value = "";
    assigned_to_select.value = "";
  }
}

function insertOptionsOnSelect(select_id, options, options_values) {
  const select = document.querySelector(select_id);

  select.innerHTML = "";

  select.innerHTML = `<option value>Todos</option>`;

  for (let i = 0; i < options.length; i++) {
    select.innerHTML += `<option value="${options_values[i]}">${options[i]}</option>`;
  }
}
