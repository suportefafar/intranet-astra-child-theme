import { Grid, html } from "https://unpkg.com/gridjs?module";

let EVENTS = [];

/*
 * LISTENER'S
 */

/*
 * Aguarda até que a DOM seja carregada para inserir os eventos no calendário
 */
document.addEventListener("DOMContentLoaded", () => {
  loadUI();
});

async function loadUI(osStatus = false) {
  let CURRENT_OS_STATUS = null;

  if (osStatus) {
    CURRENT_OS_STATUS = osStatus;
  } else {
    CURRENT_OS_STATUS = document.querySelectorAll(
      "#ul_os_status_tabs .nav-link"
    )[0].dataset.osStatus;
  }

  renderOsStatusTabs(CURRENT_OS_STATUS);

  let submissions = null;
  if (CURRENT_OS_STATUS === "minha") {
    submissions = await getEventsByAssignedTo();
  } else {
    if (CURRENT_OS_STATUS === "todas")
      submissions = await getEventsByOsStatus("");
    else submissions = await getEventsByOsStatus(CURRENT_OS_STATUS);
  }

  renderGridJS(submissions);
}

/*
 * GET EVENTS
 */
async function getEventsByOsStatus(osStatus) {
  let response;
  let status_param = osStatus ?? "";
  try {
    response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/service_tickets/by_departament?status=" +
        status_param
    );

    //console.log({ response });
  } catch (error) {
    //console.log(error.response.data.message);
    return [];
  }

  return JSON.parse(response.data);
}

async function getEventsByAssignedTo() {
  let response;
  try {
    response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/service_tickets/by_departament?assigned_to=-1"
    );

    //console.log({ response });
  } catch (error) {
    //console.log(error.response.data.message);
    return [];
  }

  return JSON.parse(response.data);
}

/**
 * UL TABS RENDER
 */

function renderOsStatusTabs(os_status = null) {
  const tabs = document.querySelectorAll("#ul_os_status_tabs .nav-link");

  tabs.forEach((el) => {
    el.addEventListener("click", onClickPlaceTabHanlder);
  });

  if (os_status) {
    changeActiveTab(os_status);
  }
}

function onClickPlaceTabHanlder(e) {
  const { osStatus } = e.target.dataset;

  loadUI(osStatus);
}

function changeActiveTab(osStatus) {
  const tabs = document.querySelectorAll("#ul_os_status_tabs .nav-link");

  tabs.forEach((el) => {
    el.classList.remove("active");
    if (el.dataset.osStatus === osStatus) el.classList.add("active");
  });
}

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
      name: "Código",
      formatter: codeColFormatter,
    },
    {
      name: "Atribuído à",
      formatter: assignedToColFormatter,
    },
    "Patrimônio",
    {
      name: "Responsável",
      formatter: (current) =>
        html(
          `<a href="/membros/${current.user_login}/" target="blank" title="${current.display_name}">${current.display_name}</a>`
        ),
    },
    {
      name: "Sala",
      formatter: (current) =>
        Array.isArray(current) && current.length !== 0
          ? current.data.number
          : "--",
    },
    {
      name: "Status",
      formatter: statusColFormatter,
    },
    "Tipo",
    {
      name: "Criado",
      formatter: (current) => parseToLocalDateTime(current),
    },
    {
      name: "Ações",
      formatter: actionColFormatter,
    },
  ],
  data: [],
  pagination: {
    limit: 20,
    summary: true,
  },
  search: true,
  sort: true,
  resizable: true,
  language: ptBR,
}).render(document.getElementById("table-wrapper"));

function renderGridJS(data = []) {
  if (!data) data = [];

  if (Array.isArray(data) && data.length === 0) data = [];

  grid
    .updateConfig({
      data: fetchDataHandler(data),
    })
    .forceRender();
}

function fetchDataHandler(submissions) {
  //console.log(submissions);

  let table_arr = [];
  for (const submission of submissions) {
    const submission_data = submission.data;

    const prevent_write = submission_data.prevent_write ? "1" : "0";
    const prevent_exec = submission_data.prevent_exec ? "1" : "0";
    const permissions = prevent_write + prevent_exec;

    const code_column_data = JSON.stringify({
      id: submission.id,
      permissions,
      code: submission_data.code,
    });

    const action_column_data = JSON.stringify({
      id: submission.id,
      permissions,
    });

    table_arr.push([
      code_column_data ?? "--",
      submission_data.assigned_to,
      submission_data.asset ?? "--",
      submission.owner.data ?? "--",
      submission_data.place,
      submission_data.status ?? "--",
      submission_data.type[0],
      submission.created_at,
      action_column_data,
    ]);
  }

  return table_arr;
}

function codeColFormatter(current, row) {
  const { id, permissions, code } = JSON.parse(current);

  const prevent_write = parseInt(permissions.split("")[0]);

  //console.log(current);

  const html_content = `
    <div class="d-flex gap-2">
      <a href="/visualizar-ordem-de-servico/?id=${id}" title="Detalhes">
        ${code}
      </a>
    </div>`;

  return html(html_content);
}

function assignedToColFormatter(current, row) {
  //console.log(current);
  if (!current.data) {
    return "--";
  }

  const html_content = `<a href="/membros/${current.data.user_login}/" target="blank" title="${current.data.display_name}">${current.data.display_name}</a>`;

  return html(html_content);
}

function statusColFormatter(current, row) {
  let type = "text-bg-info";
  const current_lower = current.toLowerCase();

  if (current_lower === "nova") type = "text-bg-success";
  else if (current_lower === "aguardando") type = "text-bg-warning";
  else if (current_lower === "em andamento") type = "text-bg-primary";
  else if (current_lower === "finalizada") type = "text-bg-secondary";
  else if (current_lower === "cancelada") type = "text-bg-danger";

  return html(`<span class="badge ${type}">${current}</span>`);
}

function actionColFormatter(current, row) {
  const { id, permissions } = JSON.parse(current);

  const prevent_write = parseInt(permissions.split("")[0]);

  //console.log(current);

  const html_content = `
    <div class="d-flex gap-2">
      <a class="btn btn-outline-primary" href="/visualizar-ordem-de-servico/?id=${id}" title="Detalhes">
        <i class="bi bi-folder2-open"></i>
      </a>
    </div>`;

  return html(html_content);
}

function parseToLocalDateTime(dateString) {
  // Parse the input string into components
  const [datePart, timePart] = dateString.split(" ");
  const [year, month, day] = datePart.split("-").map(Number);
  const [hours, minutes, seconds] = timePart.split(":").map(Number);

  // Create a UTC Date object
  const utcDate = new Date(
    Date.UTC(year, month - 1, day, hours, minutes, seconds)
  );

  // Adjust the time to GMT-3
  const gmtMinus3Date = new Date(utcDate.getTime() - 3 * 60 * 60 * 1000);

  return gmtMinus3Date.toLocaleString();
}

/*
 * Controle dos Modal's de Empréstimo e Devolução
 */
function showEventDetailsModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarEventDetailsModal")
  );

  modal.show();
}

function hideEventDetailsModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarEventDetailsModal")
  );
  modal.hide();
}
