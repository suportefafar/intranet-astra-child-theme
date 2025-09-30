let EVENTS = [];

/*
 * LISTENER'S
 */

/*
 * Aguarda até que a DOM seja carregada
 */
document.addEventListener("DOMContentLoaded", () => {
  updateURLFetchBase();
});

/*
 * Adiciona um listener para cada aba
 */
document.querySelectorAll("#ul_os_status_tabs .nav-link").forEach((el) => {
  el.addEventListener("click", (e) => {
    changeActiveTab(e);

    updateURLFetchBase();
  });
});

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
    {
      name: "Responsável",
      formatter: ownerColFormatter,
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
      name: "Criado/Atualizado",
      formatter: (current) => createdAtColFormatter(current),
    },
    {
      name: "Ações",
      formatter: actionColFormatter,
    },
  ],
  search: {
    server: {
      url: (prev, keyword) => {
        let junction = prev.indexOf("?") > 0 ? "&" : "?";
        return `${prev}${junction}keyword=${keyword}`;
      },
    },
  },
  pagination: {
    limit: 10,
    server: {
      url: (prev, page, limit) => {
        let junction = prev.indexOf("?") > 0 ? "&" : "?";
        return `${prev}${junction}limit=${limit}&offset=${page + 1}`;
      },
    },
    summary: true,
  },
  server: {
    url:
      "/wp-json/intranet/v1/submissions" +
      "/service_tickets/by_departament?assigned_to=-1&status=Nova,Aguardando,Em andamento",
    then: renderDataOnTable,
    total: (data) => data.count,
  },
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

async function updateURLFetchBase() {
  const tab_data = getActiveTabData();
  if (!tab_data) return;

  const url = "/wp-json/intranet/v1/submissions" + tab_data.url;

  // console.log({ url });
  grid
    .updateConfig({
      server: {
        url,
        then: renderDataOnTable,
        total: (data) => data.count,
      },
    })
    .forceRender();
}

async function getServiceTickets(url) {
  let response;

  try {
    response = await axios.get("/wp-json/intranet/v1/submissions" + url);

    //console.log(response.data);
  } catch (error) {
    console.log(error.response.data.message);
    return [];
  }

  return response.data;
}

function renderDataOnTable(data) {
  // Early return if data is invalid or empty
  if (!data || !Array.isArray(data.results)) {
    return [];
  }

  // Map through the results and transform each submission
  return data.results.map((submission) => {
    const { id, data, owner, updated_at, created_at, relationships } =
      submission;
    const { assigned_to, status, type, number, user_report } = data;

    const prevent_write = data.prevent_write ? "1" : "0";
    const prevent_exec = data.prevent_exec ? "1" : "0";
    const permissions = prevent_write + prevent_exec;

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

    const action_column_data = JSON.stringify({
      id,
      permissions,
      notification: data.notification ? data.notification : null,
    });

    return [
      number_column_data,
      desc_column_data,
      getSafeValue(() => relationships.applicant, null),
      status,
      getSafeValue(() => relationships.assigned_to, null),
      date_column_data,
      action_column_data,
    ];
  });
}

function numberColFormatter(current) {
  const { id, number } = JSON.parse(current);

  const html_content = `
    <div class="d-flex gap-2">
      <a href="/visualizar-ordem-de-servico/?id=${id}" target="_blank" title="Detalhes">
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

function ownerColFormatter(current) {
  let { user_login = "", display_name = "N/A" } = current ?? {};

  if (!user_login && !display_name) {
    user_login = "";
    display_name = "";
  }

  return gridjs.html(
    `<a href="/membros/${user_login}/" 
        target="blank" 
        title="${display_name}">
          ${display_name}
    </a>`
  );
}

function assignedToColFormatter(current) {
  if (!current) {
    return "--";
  }

  return gridjs.html(
    `<a href="/membros/${current.user_login}/" target="blank" title="${current.display_name}">${current.display_name}</a>`
  );
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

function actionColFormatter(current) {
  const { id, permissions, notification } = JSON.parse(current);

  let notify = false;
  if (
    notification &&
    getSafeValue(() => notification.owner.has_update, false) === true
  ) {
    notify = true;
  }

  const html_content = `
    <div class="d-flex gap-2">
      <a class="btn btn-outline-primary position-relative" href="/visualizar-ordem-de-servico/?id=${id}" target="_blank" title="Detalhes">
        <i class="bi bi-folder2-open"></i>
        ${
          notify
            ? `<span class="position-absolute top-0 start-100 translate-middle p-2 bg-success border border-light rounded-circle">
                <span class="visually-hidden">OS com Nova Atualização</span>
              </span>`
            : ""
        }
      </a>
    </div>`;

  return gridjs.html(html_content);
}
