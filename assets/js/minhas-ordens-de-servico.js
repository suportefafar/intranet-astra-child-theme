import { Grid, html } from "https://unpkg.com/gridjs?module";

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
      name: "Atribuído à",
      formatter: assignedToColFormatter,
    },
    "Departamento",
    {
      name: "Status",
      formatter: statusColFormatter,
    },
    "Tipo",
    {
      name: "Atualizado",
      formatter: (current) => parseToLocalDateTime(current),
    },
    {
      name: "Criado",
      formatter: (current) => parseToLocalDateTime(current),
    },
    {
      name: "Ações",
      formatter: actionColFormatter,
    },
  ],
  data: fetchDataHandler,
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

async function fetchDataHandler() {
  let response;

  try {
    response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/service_tickets/by_user"
    );
  } catch (error) {
    console.log(error.response.data.message);
    return [];
  }

  const submissions = JSON.parse(response.data);

  console.log(submissions);

  let table_arr = [];
  for (const submission of submissions) {
    const submission_data = submission.data;

    const prevent_write = submission_data.prevent_write ? "1" : "0";
    const prevent_exec = submission_data.prevent_exec ? "1" : "0";
    const permissions = prevent_write + prevent_exec;

    const number_column_data = JSON.stringify({
      id: submission.id,
      permissions,
      number: submission_data.number,
    });

    const action_column_data = JSON.stringify({
      id: submission.id,
      permissions,
    });

    table_arr.push([
      number_column_data,
      submission_data.assigned_to,
      submission_data.departament_assigned_to.role_display_name ?? "--",
      submission_data.status ?? "--",
      submission_data.type[0] ?? "--",
      submission.updated_at,
      submission.created_at,
      action_column_data,
    ]);
  }

  return table_arr;
}

async function renderGridJS(data = []) {
  if (!data) data = [];

  if (data.length === 0) data = await fetchDataHandler();

  grid
    .updateConfig({
      data,
    })
    .forceRender();
}

function numberColFormatter(current, row) {
  const { id, permissions, number } = JSON.parse(current);

  const prevent_write = parseInt(permissions.split("")[0]);

  //console.log(current);

  const html_content = `
    <div class="d-flex gap-2">
      <a href="/visualizar-ordem-de-servico/?id=${id}" title="Detalhes">
        ${number}
      </a>
    </div>`;

  return html(html_content);
}

function assignedToColFormatter(current, row) {
  console.log(current);
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

  console.log(current, row);

  const html_content = `
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="/visualizar-ordem-de-servico/?id=${id}" title="Detalhes">
        <i class="bi bi-info-lg"></i>
      </a>
      ${
        prevent_write
          ? ""
          : `
      <a class="btn btn-outline-secondary" href="/editar-ordem-de-servico/?id=${id}" title="Editar">
        <i class="bi bi-pencil"></i>
      </a>
      <button class="btn btn-outline-danger btn-delete-submission" data-id="${id}" title="Excluir">
        <i class="bi bi-trash"></i>
      </button> `
      }
    </div>`;

  return html(html_content);
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

  showAlert("Por favor, aguarde....", "warning");

  try {
    const response = await axios.delete(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/" + id
    );

    console.log(response);

    showAlert("Excluído com sucesso!", "success", true, 3000);

    renderGridJS();
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
