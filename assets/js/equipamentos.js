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
  grid.forceRender();
});

/*
 * Adiciona um evento de clique no botão
 * de submit dentro do modal de devolução
 */
document.addEventListener("onReturnLoanSuccess", () => {
  hideLoanOrReturnoEquipamentModal("intranetFafarLoanReturnModal");
  grid.forceRender();
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
  search: {
    server: {
      url: (prev, keyword) => {
        const url = `${prev}?keyword=${keyword}`;
        console.log(url); // Debugging: Log the search URL
        return url;
      },
    },
  },
  pagination: {
    limit: 10, // Number of rows per page
    server: {
      url: (prev, page, limit) => {
        let url = `${prev}?limit=${limit}&offset=${page + 1}`;
        if (url.indexOf("keyword") > -1)
          url = `${prev}&limit=${limit}&offset=${page + 1}`;
        console.log(url);
        return url;
      },
    },
    summary: true, // Show pagination summary
  },
  server: {
    url: "/wp-json/intranet/v1/submissions/equipaments",
    then: renderDataOnTable,
    total: (data) => data.count,
  },
  sort: true,
  resizable: true,
  autoWidth: true,
  language: ptBR,
}).render(document.getElementById("table-wrapper"));

function renderDataOnTable(data) {
  // Early return if data is invalid or empty
  if (!data || !Array.isArray(data.results)) {
    return [];
  }

  console.log(data.results);

  // Map through the results and transform each submission
  return data.results.map((submission) => {
    const { id, data: submissionData, relationships } = submission;

    // Destructure the submission data
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
      desc,
      prevent_write,
      prevent_exec,
    } = submissionData;

    // Construct the asset column data
    const assetColumnData = JSON.stringify({
      id,
      asset,
      internal_asset,
    });

    // Construct the description column data
    const descColumnData = JSON.stringify({
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
      desc,
    });

    // Construct the status column data
    const statusColumnData = JSON.stringify({
      status,
      on_loan,
    });

    // Construct the permissions string
    const permissions = `${prevent_write ? "1" : "0"}${
      prevent_exec ? "1" : "0"
    }`;

    // Construct the action column data
    const actionColumnData = JSON.stringify({
      id,
      permissions,
    });

    // Return the row data as an array
    return [
      assetColumnData,
      descColumnData,
      getSafeValue(() => relationships.place.data.number, ""),
      getSafeValue(() => relationships.applicant.display_name, ""),
      statusColumnData,
      actionColumnData,
    ];
  });
}

function assetColFormatter(current) {
  const { id, asset, internal_asset } = JSON.parse(current);

  return gridjs.html(`
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
  // Parse the input JSON string
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
    desc,
  } = JSON.parse(current);

  // Helper function to safely access array elements
  const getFirstElement = (arr) => (arr && arr.length > 0 ? arr[0] : "--");

  // Construct the custom description
  let custom_desc = "";
  if (brand && model) {
    custom_desc = `${brand} ${model}`;
  } else if (brand) {
    custom_desc = brand;
  } else if (model) {
    custom_desc = model;
  }

  // Override custom_desc for "computador" type
  if (getFirstElement(object_sub_type)?.toLowerCase() === "computador") {
    custom_desc = `${getFirstElement(cpu_brand)} ${cpu_model ?? "--"} | ${
      ram_capacity ?? "--"
    } GB | ${disk_capacity_1 ?? "--"} GB`;
  }

  // Get the OS icon based on the OS type
  const os_icon = getOsIconByOsType(getFirstElement(os_type));

  // Generate HTML content
  const htmlContent = `
    <div class="d-flex flex-column gap-1">
      <!-- Object Type -->
      <div>
        <span class="me-1"><i class="bi bi-motherboard"></i></span>
        <span>${getFirstElement(object_sub_type)}</span>
      </div>

      <!-- Custom Description -->
      <div>
        <span class="me-1"><i class="bi bi-body-text"></i></span>
        <span class="text-secondary">${custom_desc}</span>
      </div>

      <!-- OS Information -->
      ${
        os_type
          ? `
        <div>
          <span class="me-1"><i class="bi ${os_icon}"></i></span>
          <span class="text-secondary">
            ${getFirstElement(os_type)} ${os_version ?? "--"}
          </span>
        </div>
      `
          : ""
      }

      <!-- IP Address -->
      ${
        ip?.data
          ? `
        <div>
          <span class="me-1"><i class="bi bi-hdd-network"></i></span>
          <span class="text-secondary">
            ${ip.data?.address ?? ""}
          </span>
        </div>
      `
          : ""
      }

      <!-- Description -->
      ${
        desc
          ? `
        <div>
          <span class="me-1"><i class="bi bi-info"></i></span>
          <span class="text-secondary">
            ${desc}
          </span>
        </div>
      `
          : ""
      }
    </div>
  `;

  return gridjs.html(htmlContent);
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
  const { status, on_loan } = JSON.parse(current);

  const status_text = on_loan
    ? "Emprestado"
    : getSafeValue(() => status[0], "Indefinido");
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

  return gridjs.html(`<span class="badge ${type}">${status_text}</span>`);
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

  return gridjs.html(html_content);
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

  showAlert("Por favor, aguarde....", "warning", false, 0, true);

  try {
    const response = await axios.delete(
      "/wp-json/intranet/v1/submissions/" + id
    );

    console.log(response);

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
