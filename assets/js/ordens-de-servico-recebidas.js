import { Grid, html } from "https://unpkg.com/gridjs?module";

let EVENTS = [];

/*
 * LISTENER'S
 */

/*
 * Aguarda até que a DOM seja carregada
 */
document.addEventListener("DOMContentLoaded", () => {
  fetchTableData();
});

/*
 * Adiciona um listener para cada aba
 */
document.querySelectorAll("#ul_os_status_tabs .nav-link").forEach((el) => {
  el.addEventListener("click", (e) => {
    changeActiveTab(e);

    fetchTableData();
  });
});

function changeActiveTab(el) {
  const tabs = document.querySelectorAll("#ul_os_status_tabs .nav-link");

  tabs.forEach((tab) => {
    if (tab === el.target) {
      tab.classList.add("active");
    } else {
      tab.classList.remove("active");
    }
  });
}

function getActiveTabData() {
  const tab_el = getActiveTab();

  if (!tab_el) {
    showAlert(
      "Ocorreu algum erro! Por favor, contate o setor de T.I.",
      "danger"
    );
    console.error("Nenhuma aba ativada");
    return null;
  }

  const { url } = tab_el.dataset;

  if (!url) {
    showAlert(
      "Ocorreu algum erro! Por favor, contate o setor de T.I.",
      "danger"
    );
    console.error("O elemento não contém os dados necessários (url).");
    return null;
  }

  return { url };
}

function getActiveTab() {
  return document.querySelector("#ul_os_status_tabs .nav-link.active");
}

async function fetchTableData() {
  const tab_data = getActiveTabData();
  if (!tab_data) return;

  const submissions = await getServiceTickets(tab_data.url);
  renderTable(submissions);
}

async function getServiceTickets(url) {
  let response;

  try {
    response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions" + url
    );

    //console.log(response.data);
  } catch (error) {
    console.log(error.response.data.message);
    return [];
  }

  return JSON.parse(response.data);
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
      name: "Descrição",
      formatter: descColFormatter,
    },
    {
      name: "Responsável",
      formatter: (current) =>
        html(
          `<a href="/membros/${current.user_login}/" target="blank" title="${current.display_name}">${current.display_name}</a>`
        ),
    },
    {
      name: "Status",
      formatter: statusColFormatter,
    },
    {
      name: "Atribuído à",
      formatter: assignedToColFormatter,
    },
    {
      name: "Criado",
      formatter: (current) => createdAtColFormatter(current),
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
  autoWidth: true,
  language: ptBR,
}).render(document.getElementById("table-wrapper"));

function renderTable(data = []) {
  if (!data) data = [];

  if (Array.isArray(data) && data.length === 0) data = [];

  grid
    .updateConfig({
      data: fetchDataHandler(data),
    })
    .forceRender();
}

function fetchDataHandler(submissions) {
  console.log(submissions);

  let table_arr = [];
  for (const submission of submissions) {
    const { id, data, owner, updated_at, created_at } = submission;
    const { assigned_to, status, type, code, user_report } = data;

    const prevent_write = data.prevent_write ? "1" : "0";
    const prevent_exec = data.prevent_exec ? "1" : "0";
    const permissions = prevent_write + prevent_exec;

    const code_column_data = JSON.stringify({
      id,
      permissions,
      code,
    });

    const desc_column_data = JSON.stringify({
      type,
      user_report,
    });

    const date_column_data = JSON.stringify({
      updated_at,
      created_at,
    });

    const action_column_data = JSON.stringify({
      id,
      permissions,
    });

    table_arr.push([
      code_column_data ?? "--",
      desc_column_data,
      owner.data ?? "--",
      status ?? "--",
      assigned_to,
      date_column_data,
      action_column_data,
    ]);
  }

  return table_arr;
}

function codeColFormatter(current) {
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

function descColFormatter(current) {
  const { type, user_report } = JSON.parse(current);

  const MAX_CHAR_DESC = 100;

  return html(`
    <div class="d-flex flex-column gap-1">
      <div>
        <span>
          ${type}
        </span>
      </div>

      <div>
        <span class="text-secondary">
        ${
          user_report.length > MAX_CHAR_DESC
            ? user_report.slice(0, MAX_CHAR_DESC) + "..."
            : user_report
        }
        </span>
      </div>
    </div
    `);
}

function assignedToColFormatter(current) {
  //console.log(current);
  if (!current.data) {
    return "--";
  }

  const html_content = `<a href="/membros/${current.data.user_login}/" target="blank" title="${current.data.display_name}">${current.data.display_name}</a>`;

  return html(html_content);
}

function statusColFormatter(current) {
  let type = "text-bg-info";

  switch (current.toLowerCase()) {
    case "nova":
      type = "text-bg-success";
      break;

    case "aguardando":
      type = "text-bg-warning";
      break;

    case "em andamento":
      type = "text-bg-primary";
      break;

    case "finalizada":
      type = "text-bg-secondary";
      break;

    case "cancelada":
      type = "text-bg-danger";
      break;
  }

  return html(`<span class="badge ${type}">${current}</span>`);
}

function createdAtColFormatter(current) {
  const { updated_at, created_at } = JSON.parse(current);

  const formatted_created_at = new Date(created_at).toLocaleString();

  const formatted_updated_at = new Date(updated_at).toLocaleString();

  const how_long_created_at = getDateAsHowLongFormatted(created_at);

  const how_long_updated_at = getDateAsHowLongFormatted(updated_at);

  return html(`
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

function actionColFormatter(current) {
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

function getDateAsHowLongFormatted(d) {
  const date = new Date(d);
  const now = new Date();

  const diff_seconds = (now.getTime() - date.getTime()) / 1000;

  if (diff_seconds < 60) {
    return "Agora";
  } else if (diff_seconds < 60 * 60) {
    return (diff_seconds / 60).toFixed(0) + "min";
  } else if (diff_seconds < 60 * 60 * 24) {
    return (diff_seconds / (60 * 60)).toFixed(0) + "h";
  } else if (diff_seconds < 60 * 60 * 24 * 30) {
    return (diff_seconds / (60 * 60 * 24)).toFixed(0) + "d";
  } else {
    return date.toLocaleDateString();
  }
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
