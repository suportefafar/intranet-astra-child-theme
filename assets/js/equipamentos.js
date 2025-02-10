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
 * Adiciona um evento de clique à DOM,
 * e despara se o elemento que recebeu o clique tem
 * a classe 'btn-loan-equipament' ou é filho de um elemento
 * com essa classe
 */
document.addEventListener("click", (event) => {
  const loanButton = event.target.closest(".btn-loan-equipament");

  if (loanButton) {
    const id = loanButton.dataset.id;
    document.querySelector("#equipament_to_loan").value = id;
    showLoanOrReturnoEquipamentModal("intranetFafarLoanModal");
  }
});

/*
 * Adiciona um evento de clique à DOM,
 * e despara se o elemento que recebeu o clique tem
 * a classe 'btn-loan-return-equipament' ou é filho de um elemento
 * com essa classe
 */
document.addEventListener("click", (event) => {
  const loanButton = event.target.closest(".btn-loan-return-equipament");

  if (loanButton) {
    const id = loanButton.dataset.id;
    document.querySelector("#equipament_to_return").value = id;
    showLoanOrReturnoEquipamentModal("intranetFafarLoanReturnModal");
  }
});

/*
 * Adiciona um evento de clique no botão
 * de submit dentro do modal de empréstimo
 */
document.addEventListener("onLoanSuccess", () => {
  hideLoanOrReturnoEquipamentModal("intranetFafarLoanModal");
  renderGridJS();
});

/*
 * Adiciona um evento de clique no botão
 * de submit dentro do modal de devolução
 */
document.addEventListener("onReturnLoanSuccess", () => {
  hideLoanOrReturnoEquipamentModal("intranetFafarLoanReturnModal");
  renderGridJS();
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
    { name: "Patrimônio", formatter: assetColFormatter },
    { name: "Descrição", formatter: descColFormatter },
    "Sala",
    "Responsável",
    {
      name: "Status",
      formatter: statusColFormatter,
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
    response = await axios.get("/wp-json/intranet/v1/submissions/equipaments");
  } catch (error) {
    console.log(error.response.data.message);
    return [];
  }

  const submissions = JSON.parse(response.data);

  console.log(submissions);

  let table_arr = [];
  for (const submission of submissions) {
    const { id, data } = submission;
    const {
      status,
      on_loan,
      asset,
      internal_asset,
      object_sub_type,
      brand,
      model,
      ip,
      place,
      applicant,
      cpu_brand,
      cpu_model,
      ram_capacity,
      disk_capacity_1,
      os_type,
      os_version,
    } = data;

    const asset_column_data = JSON.stringify({
      id,
      asset,
      internal_asset,
    });

    const desc_column_data = JSON.stringify({
      object_sub_type,
      brand,
      model,
      ip,
      cpu_brand,
      cpu_model,
      ram_capacity,
      disk_capacity_1,
      os_type,
      os_version,
    });

    const status_column_data = JSON.stringify({
      status,
      on_loan,
    });

    const prevent_write = data.prevent_write ? "1" : "0";
    const prevent_exec = data.prevent_exec ? "1" : "0";
    const permissions = prevent_write + prevent_exec;

    const action_column_data = JSON.stringify({
      id: submission.id,
      permissions,
    });

    table_arr.push([
      asset_column_data,
      desc_column_data,
      place.data?.number ?? "",
      applicant ?? "",
      status_column_data,
      action_column_data,
    ]);
  }

  return table_arr;
}

function assetColFormatter(current) {
  const { id, asset, internal_asset } = JSON.parse(current);

  return html(`
      <div class="d-flex flex-column gap-1">
        <div>
          <span class="me-1"><i class="bi bi-upc-scan"></i></span>
          <span>
            <a href="/visualizar-equipamento/?id=${id}" 
               class="text-decoration-none" 
               target="blank" 
               title="Detalhes do patrimônio ${asset}">
              ${asset}
            </a>
          </span>
        </div>
  
        ${
          internal_asset
            ? `<div>
          <span class="me-1"><i class="bi bi-upc"></i></span>
          <span class="text-secondary">
            <a href="/visualizar-equipamento/?id=${id}" 
               class="text-decoration-none" 
               target="blank" 
               title="Detalhes do patrimônio interno ${internal_asset}">
              ${internal_asset}
            </a>
          </span>
        </div>`
            : ""
        }
      </div>
      `);
}

function descColFormatter(current) {
  const {
    object_sub_type,
    brand,
    model,
    ip,
    cpu_brand,
    cpu_model,
    ram_capacity,
    disk_capacity_1,
    os_type,
    os_version,
  } = JSON.parse(current);

  let desc = "";
  if (brand && model) desc = `${brand} ${model}`;
  else if (brand) desc = brand;
  else if (model) desc = model;

  if (object_sub_type[0] && object_sub_type[0].toLowerCase() === "computador") {
    desc = `${cpu_brand[0] ?? "--"} ${cpu_model ?? "--"} | ${
      ram_capacity ?? "--"
    } GB | ${disk_capacity_1 ?? "--"} GB`;
  }

  const os_icon = getOsIconByOsType(os_type ? os_type[0] : null);

  return html(`
      <div class="d-flex flex-column gap-1">
        <div>
          <span class="me-1"><i class="bi bi-motherboard"></i></span>
          <span>
            ${object_sub_type[0] ?? "--"}
          </span>
        </div>
  
        <div>
          <span class="me-1"><i class="bi bi-body-text"></i></span>
          <span class="text-secondary">
            ${desc}
          </span>
        </div>

        ${
          os_type
            ? `<div>
          <span class="me-1"><i class="bi ${os_icon}"></i></span>
          <span class="text-secondary">
          ${os_type[0] ?? "--"} ${os_version ?? "--"}
          </span>
        </div>`
            : ""
        }

        ${
          ip.data
            ? `<div>
          <span class="me-1"><i class="bi bi-hdd-network"></i></span>
          <span class="text-secondary">
            ${ip.data?.address ?? ""}
          </span>
        </div>
      </div>`
            : ""
        }
      `);
}

function getOsIconByOsType(type) {
  if (!type) return "bi-gear-wide-connected";

  switch (type.toLowerCase()) {
    case "windows":
      return "bi-windows";

    case "mac os":
      return "bi-apple";

    case "macos":
      return "bi-apple";

    case "osx":
      return "bi-apple";

    case "apple":
      return "bi-apple";

    case "ios":
      return "bi-apple";

    case "linux":
      return "bi-tencent-qq";

    case "android":
      return "bi-android2";

    default:
      return "bi-gear-wide-connected";
  }
}

function statusColFormatter(current) {
  try {
    const { status, on_loan } = JSON.parse(current);

    const status_text = on_loan ? "Emprestado" : status[0] || "Indefinido";
    const normalizedStatus = status_text.toLowerCase();

    let type = "text-bg-info";
    switch (normalizedStatus) {
      case "emprestado":
        type = "text-bg-warning";
        break;
      case "ativado":
        type = "text-bg-primary";
        break;
      case "desativado":
      case "quebrado":
      case "desaparecido":
        type = "text-bg-danger";
        break;
    }

    return html(`<span class="badge ${type}">${status_text}</span>`);
  } catch (error) {
    console.error("Invalid JSON input:", current, error);
    return html('<span class="badge text-bg-secondary">Erro</span>');
  }
}

function actionColFormatter(current) {
  const { id, permissions } = JSON.parse(current);

  const prevent_write = parseInt(permissions.split("")[0]);

  const html_content = `
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="/visualizar-equipamento/?id=${id}" title="Detalhes">
        <i class="bi bi-info-lg"></i>
      </a>
      <button class="btn btn-outline-primary btn-loan-equipament" data-id="${id}" title="Emprestar">
        <i class="bi bi-arrow-up"></i>
      </button>
      <button class="btn btn-outline-primary btn-loan-return-equipament" data-id="${id}" title="Receber">
        <i class="bi bi-arrow-down"></i>
      </button>
      ${
        prevent_write
          ? ""
          : `
      <a class="btn btn-outline-secondary" href="/editar-equipamento/?id=${id}" title="Editar">
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
    "Excluir Diskiplina?",
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
      "/wp-json/intranet/v1/submissions/" + id
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

/*
 * Controle dos Modal's de Empréstimo e Devolução
 */
function showLoanOrReturnoEquipamentModal(modal_id) {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById(modal_id)
  );

  modal.show();
}

function hideLoanOrReturnoEquipamentModal(modal_id) {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById(modal_id)
  );
  modal.hide();
}
