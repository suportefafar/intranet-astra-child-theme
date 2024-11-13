import { Grid, html } from "https://unpkg.com/gridjs?module";

/**
 * CHARTS RENDER
 */

/**
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
    "Número",
    "Descrição",
    {
      name: "Andar",
      formatter: (current) => current + "ª",
    },
    "Bloco",
    {
      name: "Tipo",
      formatter: typeColFormatter,
    },
    {
      name: "Ações",
      formatter: actionColFormatter,
    },
  ],
  data: fetchDataHandler,
  pagination: {
    limit: 10,
    summary: true,
  },
  search: true,
  sort: true,
  resizable: true,
  language: ptBR,
}).render(document.getElementById("table-wrapper"));

async function fetchDataHandler() {
  let response;

  try {
    response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/object/place"
    );
  } catch (error) {
    console.log(error.response.data.message);
    return [];
  }

  const submissions = JSON.parse(response.data);

  console.log(submissions);

  const submissions_sorted = submissions.sort(
    (a, b) => a.data.number - b.data.number
  );

  let table_arr = [];
  for (const submission of submissions_sorted) {
    const submission_data = submission["data"];

    console.log(JSON.stringify(submission_data));

    const prevent_write = submission["prevent_write"] ? "1" : "0";
    const prevent_exec = submission["prevent_exec"] ? "1" : "0";
    const permissions = prevent_write + prevent_exec;

    const action_column_data = JSON.stringify({
      id: submission["id"],
      permissions,
      object_sub_type: submission_data["object_sub_type"],
    });

    table_arr.push([
      submission_data["number"],
      submission_data["desc"],
      submission_data["floor"],
      submission_data["block"],
      submission_data["object_sub_type"].toString(),
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

function typeColFormatter(current) {
  switch (current) {
    case "general":
      return "Geral";
    case "classroom":
      return "Sala de aula";
    case "professor_office":
      return "Gabinete";
    case "lab":
      return "Laboratório";
    case "auditorium":
      return "Auditório";
    default:
      return "--";
  }
}

function actionColFormatter(current, row) {
  console.log(current);
  const { id, permissions, object_sub_type } = JSON.parse(current);

  const prevent_write = parseInt(permissions.split("")[0]);

  console.log(current, row);

  const html_content = `
    <div class="d-flex gap-2">

      <a class="btn btn-outline-secondary" href="/visualizar-objeto/?id=${id}" title="Detalhes">
        <i class="bi bi-info-lg"></i>
      </a>

      ${
        object_sub_type === "classroom" || object_sub_type === "auditorium"
          ? `<a class="btn btn-outline-primary" href="/reservas/?sala=${id}" title="Eventos">
          <i class="bi bi-calendar-week"></i>
        </a>`
          : ""
      }
      
      ${
        prevent_write
          ? ""
          : `

      <a class="btn btn-outline-secondary" href="/editar-sala/?id=${id}" title="Editar">
        <i class="bi bi-pencil"></i>
      </a>

      <button class="btn btn-outline-danger btn-delete-submission" data-id="${id}" title="Excluir">
        <i class="bi bi-trash"></i>
      </button> 
      `
      }
    </div>`;

  return html(html_content);
}

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
