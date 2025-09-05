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
    {
      name: "Departamento",
      formatter: deparmentColFormatter,
    },
    {
      name: "Status",
      formatter: statusColFormatter,
    },
    {
      name: "Criado/Atualizado",
      formatter: createdAtColFormatter,
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
    url: "/wp-json/intranet/v1/submissions/service_tickets/by_user",
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

function renderDataOnTable(data) {
  // Early return if data is invalid or empty
  if (!data || !Array.isArray(data.results)) {
    return [];
  }

  // Map through the results and transform each submission
  return data.results.map((submission) => {
    const { id, data, updated_at, created_at } = submission;
    const {
      assigned_to,
      status,
      type,
      number,
      user_report,
      departament_assigned_to,
    } = data;

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

    const departament_column_data = JSON.stringify({
      assigned_to,
      departament_assigned_to,
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
    // submission_data.departament_assigned_to.role_display_name
    return [
      number_column_data,
      desc_column_data,
      departament_column_data,
      status,
      date_column_data,
      action_column_data,
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

function deparmentColFormatter(current) {
  const { assigned_to, departament_assigned_to } = JSON.parse(current);

  return gridjs.html(`
    <div class="d-flex flex-column gap-1">
      <div>
        <i class="bi bi-bookmark"></i>
        <span>${departament_assigned_to.role_display_name ?? "--"}</span>
      </div>

      <div>
        <i class="bi bi-person"></i>
        ${
          assigned_to.data
            ? `<a href="/membros/${assigned_to.data.user_login}/" target="blank" title="${assigned_to.data.display_name}">${assigned_to.data.display_name}</a>`
            : "--"
        }
        
      </div>
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

function actionColFormatter(current) {
  const { id, permissions, notification } = JSON.parse(current);

  const prevent_write = parseInt(permissions.split("")[0]);

  let notify = false;
  if (
    notification &&
    getSafeValue(notification.owner.has_update, false) === true
  ) {
    notify = true;
  }

  const html_content = `
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary position-relative" href="/visualizar-ordem-de-servico/?id=${id}" target="_blank" title="Detalhes">
        <i class="bi bi-info-lg"></i>
        ${
          notify
            ? `<span class="position-absolute top-0 start-100 translate-middle p-2 bg-success border border-light rounded-circle">
                <span class="visually-hidden">OS com Nova Atualização</span>
              </span>`
            : ""
        }
      </a>
      ${
        prevent_write
          ? ""
          : `
      <a class="btn btn-outline-secondary" href="/editar-ordem-de-servico/?id=${id}" target="_blank" title="Editar">
        <i class="bi bi-pencil"></i>
      </a>
      <button class="btn btn-outline-danger btn-delete-submission" data-id="${id}" title="Excluir">
        <i class="bi bi-trash"></i>
      </button> `
      }
    </div>`;

  return gridjs.html(html_content);
}

function confirmDelete(id) {
  showConfirmModal(
    "Excluir Disciplina?",
    "Essa ação não pode ser desfeita.",
    "Excluir",
    "danger",
    () => deleteSubmission(id)
  );
}

async function deleteSubmission(id) {
  hideConfirmModal();

  showAlert("Por favor, aguarde....", "warning", false, 0, true);

  try {
    const response = await axios.delete(
      "/wp-json/intranet/v1/submissions/" + id
    );

    showAlert("Excluído com sucesso!", "success", true, 3000);

    grid.forceRender();
  } catch (error) {
    let error_msg = "[1010]Unknow error on try catch";

    if (error.response?.data?.message) {
      console.log(error.response.data);
      error_msg = error.response.data.message;
    } else {
      console.log(error);
    }

    showAlert(error_msg, "danger");
  }
}
