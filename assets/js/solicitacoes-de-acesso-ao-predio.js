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
  if (button) {
    const id = button.dataset?.id;

    if (id) {
      renderAccessBuildingRequestDetailsModal(id);
      showAccessBuildingRequestDetailsModal();
    }
  }
});

/*
 * Adiciona um evento de clique à DOM,
 * e despara se o elemento que recebeu o clique tem
 * a classe 'btn-register-entry' ou é filho de um elemento
 * com essa classe
 */

document.addEventListener("click", handleRegisterClick);

function handleRegisterClick(event) {
  const buttonEntry = event.target.closest(".btn-register-entry");
  const buttonExit = event.target.closest(".btn-register-exit");

  if (buttonEntry) {
    console.log("addEventListener ENTRY", new Date().toLocaleString());

    event.stopImmediatePropagation(); // Evita múltiplas execuções no mesmo clique

    const id = buttonEntry.dataset?.id;
    if (id) {
      confirmRegister(
        "Registrar Entrada?",
        "Essa ação não pode ser desfeita.",
        id,
        "entry"
      );
    }
  }

  if (buttonExit) {
    console.log("addEventListener EXIT", new Date().toLocaleString());

    event.stopImmediatePropagation(); // Evita múltiplas execuções no mesmo clique

    const id = buttonExit.dataset?.id;
    if (id) {
      confirmRegister(
        "Registrar Saída?",
        "Essa ação não pode ser desfeita.",
        id,
        "exit"
      );
    }
  }
}

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
    { name: "Usuário", formatter: userColFormatter },
    { name: "Local", formatter: placeColFormatter },
    { name: "Período", formatter: periodColFormatter },
    { name: "Últ. Status", formatter: lastRegisterStatusFormatter },
    { name: "Ações", formatter: actionColFormatter },
  ],
  data: fetchDataHandler,
  pagination: {
    limit: 10,
    summary: true,
  },
  search: true,
  sort: true,
  resizable: true,
  autoWidth: true,
  language: ptBR,
}).render(document.getElementById("table-wrapper"));

async function renderGridJS(data = []) {
  if (!data) data = [];

  if (data.length === 0) data = await fetchDataHandler();

  grid
    .updateConfig({
      data,
    })
    .forceRender();
}

async function fetchDataHandler() {
  let response;

  try {
    response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/access_building_request/"
    );
  } catch (error) {
    //console.log(error.response.data.message);
    return [];
  }

  const submissions = response.data;

  // console.log(submissions);

  let table_arr = [];
  for (const submission of Object.values(submissions)) {
    const { data, owner } = submission;
    const {
      object_sub_type,
      access_building_request_type,
      third_party_name,
      place,
      lab,
      start_date,
      end_date,
      logs,
    } = data;

    const user_column_data = JSON.stringify({
      access_building_request_type,
      third_party_name,
      owner,
    });

    const place_column_data = JSON.stringify({
      place,
      lab,
    });

    const period_column_data = JSON.stringify({
      start_date,
      end_date,
    });

    const prevent_write = submission.prevent_write ? "1" : "0";
    const prevent_exec = submission.prevent_exec ? "1" : "0";
    const permissions = prevent_write + prevent_exec;

    const action_column_data = JSON.stringify({
      id: submission.id,
      permissions,
      object_sub_type,
    });

    table_arr.push([
      user_column_data,
      place_column_data,
      period_column_data,
      logs,
      action_column_data,
    ]);
  }

  return table_arr;
}

function userColFormatter(current) {
  const { access_building_request_type, third_party_name, owner } =
    JSON.parse(current);

  return html(`
      <div class="d-flex flex-column gap-1">
        <div>
          <span class="me-1"><i class="bi bi-person-up"></i></span>
          <span>
            ${access_building_request_type ?? ""}
          </span>
        </div>

        <div>
          <span class="me-1"><i class="bi bi-person"></i></span>
          <span>
            <a href="/membros/${owner.data.user_login}/" 
               class="text-decoration-none" 
               target="blank" 
               title="${owner.data.display_name}">
              ${owner.data.display_name}
            </a>
          </span>
        </div>

        <div>
          <span class="me-1"><i class="bi bi-person-plus"></i></span>
          <span>
            ${third_party_name ?? ""}
          </span>
        </div>
      </div>
      `);
}

function placeColFormatter(current) {
  const { place, lab } = JSON.parse(current);

  return html(`
      <div>
        <span>
          <a href="https://intranet.farmacia.ufmg.br/visualizar-objeto/?id=${
            place.id
          }" 
             class="text-decoration-none" 
             target="blank" 
             title="Detalhes da sala ${place.data?.number ?? ""}">
            ${place.data?.number ?? ""}
          </a>
        </span>
        
        ${lab ? `<span>(${lab})</span>` : ""}
       
      </div>`);
}

function periodColFormatter(current) {
  const { start_date, end_date } = JSON.parse(current);

  return html(`
      <div class="d-flex flex-column gap-1">
        <div>
          <span class="me-1"><i class="bi bi-clock"></i></span>
          <span>
            ${new Date(start_date).toLocaleDateString()} - ${new Date(
    end_date
  ).toLocaleDateString()}
          </span>
        </div>
      </div>
      `);
}

function lastRegisterStatusFormatter(current) {
  let type = "text-bg-info";
  let text = "Não utilizado";

  if (current && current.length > 0) {
    const last_update = current[current.length - 1];

    if (last_update.type == "entry") {
      type = "text-bg-success";
      text = "Entrada";
    } else if (last_update.type == "exit") {
      type = "text-bg-danger";
      text = "Saída";
    }
  }

  let last_register = "--";

  if (current && current.length > 0) {
    const last_update = current[current.length - 1];

    last_register = new Date(last_update.registered_at * 1000).toLocaleString();
  }

  return html(`
    <div class="d-flex gap-1 align-items-center">
      <span>
        <i class="bi bi-clock"></i>
      </span>
      <span>
        ${last_register}
      </span>
      <span class="badge ${type}">${text}</span>
    </div>
    `);
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

function confirmRegister(title = "", text = "", id = null, type = null) {
  console.log("confirmRegister", new Date().toLocaleString());

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
  console.log("registerEntryOrExit", new Date().toLocaleString());
  hideConfirmModal();
  showAlert("Por favor, aguarde...", "warning");

  const endpoint = `https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/access_building_request/${id}/register/`;

  try {
    const response = await axios.put(endpoint, { type });

    // console.log("Registration successful:", response.data);

    showAlert("Registrado com sucesso!", "success", true, 3000);

    renderGridJS();
  } catch (error) {
    let errorMessage = "[1010] Unknown error occurred.";

    if (error.response?.data?.message) {
      console.error("Registration failed:", error.response.data);
      errorMessage = error.response.data.message;
    } else {
      console.error("An unexpected error occurred:", error);
    }

    showAlert(errorMessage, "danger");
  }
}

async function getAccessBuildingRequestByID(id) {
  showAlert("Por favor, aguarde....", "warning");

  try {
    const response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/" + id
    );

    // console.log(response);

    hideAlert();

    return response;
  } catch (error) {
    let error_msg = "[1010] Unknow error on try catch";

    if (error.response?.data?.message) {
      console.log(error.response.data);
      error_msg = error.response.data.message;
    } else {
      console.log(error);
    }

    showAlert(error_msg, "danger");

    return null;
  }
}

async function renderAccessBuildingRequestDetailsModal(id) {
  const response = await getAccessBuildingRequestByID(id);

  if (!response) {
    hideAccessBuildingRequestDetailsModal();
  }

  const access_building_request = response.data;

  document.querySelector("#access_building_request_created_at").innerHTML =
    new Date(access_building_request["created_at"]).toLocaleString();

  document.querySelector("#access_building_request_start_date").innerHTML =
    new Date(
      access_building_request["data"]["start_date"]
    ).toLocaleDateString();

  document.querySelector("#access_building_request_end_date").innerHTML =
    new Date(access_building_request["data"]["end_date"]).toLocaleDateString();

  document.querySelector("#access_building_request_owner").innerHTML =
    access_building_request["owner"]["data"]["display_name"];

  document.querySelector("#access_building_request_type").innerHTML =
    access_building_request["data"]["access_building_request_type"];

  document.querySelector(
    "#access_building_request_third_party_name"
  ).innerHTML = access_building_request["data"]["third_party_name"];

  document.querySelector(
    "#access_building_request_third_party_sector"
  ).innerHTML = access_building_request["data"]["third_party_sector"];

  document.querySelector("#access_building_request_place").innerHTML =
    access_building_request["data"]["place"]["data"]["number"];

  document.querySelector("#access_building_request_justification").innerHTML =
    access_building_request["data"]["justification_for_request"];

  let logs_html = "";

  for (const log of access_building_request["data"]["logs"]) {
    let type = "";
    let text = "";

    if (log["type"] == "entry") {
      type = "text-bg-success";
      text = "Entrada";
    } else if (log["type"] == "exit") {
      type = "text-bg-danger";
      text = "Saída";
    } else {
      type = "text-bg-info";
      text = log["type"];
    }

    logs_html += `
    <tr>
      <td class="text-body">${new Date(
        log["registered_at"] * 1000
      ).toLocaleString()}</td>
      <td id="access_building_request_type" class="text-body-emphasis"><span class="badge ${type}">${text}</span></td>
    </tr>`;
  }

  document.querySelector("#access_building_request_logs tbody").innerHTML =
    logs_html ?? "Sem registro";
}

/*
 * Controle do modal de Detalhes
 */
function showAccessBuildingRequestDetailsModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarAccessBuildingRequestDetailsModal")
  );

  modal.show();
}

function hideAccessBuildingRequestDetailsModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarAccessBuildingRequestDetailsModal")
  );
  modal.hide();
}
