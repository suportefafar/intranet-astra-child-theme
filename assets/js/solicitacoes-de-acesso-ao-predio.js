import { Grid, html } from "https://unpkg.com/gridjs?module";

/*
 * LISTENER's
 */

/*
 * Adiciona um evento de clique à DOM,
 * e despara se o elemento que recebeu o clique tem
 * a classe 'btn-submission-details' ou é filho de um elemento
 * com essa classe
 */
document.addEventListener("click", (event) => {
  const button = event.target.closest(".btn-submission-details");
  const id = button.dataset.id;

  if (id) {
    showAccessRequestDetailsModal();
  }
});

/*
 * Adiciona um evento de clique à DOM,
 * e despara se o elemento que recebeu o clique tem
 * a classe 'btn-register-entry' ou é filho de um elemento
 * com essa classe
 */
document.addEventListener("click", (event) => {
  const button = event.target.closest(".btn-register-entry");
  const id = button.dataset.id;

  if (id) {
    confirmRegister(
      "Registrar Entrada?",
      "Essa ação não pode ser desfeita.",
      id,
      "entry"
    );
  }
});

/*
 * Adiciona um evento de clique à DOM,
 * e despara se o elemento que recebeu o clique tem
 * a classe 'btn-register-exit' ou é filho de um elemento
 * com essa classe
 */
document.addEventListener("click", (event) => {
  const button = event.target.closest(".btn-register-exit");
  const id = button.dataset.id;

  if (id) {
    confirmRegister(
      "Registrar Saída?",
      "Essa ação não pode ser desfeita.",
      id,
      "exit"
    );
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
    "Tipo",
    { name: "Solicitante", formatter: (current) => current },
    "Terceiro",
    {
      name: "Local",
      formatter: (current) => current?.data?.number ?? "",
    },
    "Laboratório",
    {
      name: "Início",
      formatter: (current) => new Date(current).toLocaleDateString(),
    },
    {
      name: "Fim",
      formatter: (current) => new Date(current).toLocaleDateString(),
    },
    { name: "Últ. Status", formatter: lastRegisterStatusFormatter },
    { name: "Últ. Registro", formatter: lastRegisterFormatter },
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
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/object/access_building_request/"
    );
  } catch (error) {
    //console.log(error.response.data.message);
    return [];
  }

  const submissions = JSON.parse(response.data);

  console.log(submissions);

  let table_arr = [];
  for (const submission of Object.values(submissions)) {
    const submission_data = submission["data"];

    //console.log(JSON.stringify(submission_data));

    const prevent_write = submission["prevent_write"] ? "1" : "0";
    const prevent_exec = submission["prevent_exec"] ? "1" : "0";
    const permissions = prevent_write + prevent_exec;

    const action_column_data = JSON.stringify({
      id: submission["id"],
      permissions,
      object_sub_type: submission_data["object_sub_type"],
    });

    table_arr.push([
      submission_data["access_building_request_type"],
      submission_data["owner"],
      submission_data["third_party_name"],
      submission_data["place"],
      submission_data["lab"],
      submission_data["start_date"],
      submission_data["end_date"],
      new Date().toLocaleString(),
      new Date().toLocaleString(),
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

function lastRegisterStatusFormatter(current) {
  let type = "text-bg-info";
  const current_lower = current.toLowerCase();

  if (current_lower === "nova") type = "text-bg-success";
  else type = "text-bg-danger";

  return html(`<span class="badge ${type}">Saída</span>`);
}

function lastRegisterFormatter(current) {
  return current;
}

function actionColFormatter(current) {
  //console.log(current);
  const { id, permissions, object_sub_type } = JSON.parse(current);

  const prevent_write = parseInt(permissions.split("")[0]);

  //console.log(current, row);

  const html_content = `
    <div class="d-flex gap-2">
      
      ${
        prevent_write
          ? ""
          : `
      
          <button class="btn btn-outline-secondary btn-submission-details" data-id="${id}" title="Detalhes">
            <i class="bi bi-info-lg"></i>
          </button>

          <button class="btn btn-outline-success btn-register-entry" data-id="${id}" title="Registrar entrada">
            <i class="bi bi-building-up"></i>
          </button>

          <button class="btn btn-outline-danger btn-register-exit" data-id="${id}" title="Registrar saída">
            <i class="bi bi-building-down"></i>
          </button>
      `
      }
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

function confirmRegister(title = "", text = "", id = null, type = null) {
  if (id === null || type === null) {
    alert("Nenhum ID ou tipo de registro informado! Contacte o administrador.");
    return;
  }

  let style_class = "primary";
  if (type === "entry") {
    style_class = "success";
  } else if (type === "exit") {
    style_class = "danger";
  }

  showConfirmModal(title, text, "Registrar", style_class, () =>
    registerEntryOrExit(id, type)
  );
}

async function registerEntryOrExit(id, type) {
  hideConfirmModal();

  showAlert("Por favor, aguarde....", "warning");

  try {
    const response = await axios.delete(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/" + id
    );

    console.log(response);

    showAlert("Registrado com sucesso!", "success", true, 3000);

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

/*
 * Controle do modal de Detalhes
 */
function showAccessRequestDetailsModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarAccessRequestDetailsModal")
  );

  modal.show();
}

function hideAccessRequestDetailsModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarAccessRequestDetailsModal")
  );
  modal.hide();
}
