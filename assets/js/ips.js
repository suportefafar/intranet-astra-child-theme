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
    "IP",
    "Hostname",
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
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/ips"
    );
  } catch (error) {
    return [];
  }

  const submissions = response.data;

  console.log(submissions);

  const submissions_sorted = submissions.sort((a, b) => {
    const numA = a.data.address.split(".").map(Number);
    const numB = b.data.address.split(".").map(Number);

    for (let i = 0; i < 4; i++) {
      if (numA[i] !== numB[i]) {
        return numA[i] - numB[i];
      }
    }
    return 0;
  });

  let table_arr = [];
  for (const submission of submissions_sorted) {
    const { id, data, is_active } = submission;
    const { address, hostname, equipament_id } = data;

    const status_column_data = JSON.stringify({
      equipament_id,
      is_active,
    });

    const prevent_write = submission.prevent_write ? "1" : "0";
    const prevent_exec = submission.prevent_exec ? "1" : "0";
    const permissions = prevent_write + prevent_exec;

    const action_column_data = JSON.stringify({
      id,
      permissions,
      equipament_id,
    });

    table_arr.push([address, hostname, status_column_data, action_column_data]);
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

function statusColFormatter(current) {
  const { equipament_id, is_active } = JSON.parse(current);

  let status_text = "Indisponível";
  let type = "text-bg-danger";

  if (is_active === "0") {
    status_text = "Desativado";
    type = "text-bg-dark";
  } else if (!equipament_id) {
    status_text = "Disponível";
    type = "text-bg-success";
  }

  return gridjs.html(`<span class="badge ${type}">${status_text}</span>`);
}

function actionColFormatter(current, row) {
  const { id, permissions, equipament_id } = JSON.parse(current);

  const prevent_write = parseInt(permissions.split("")[0]);

  const html_content = `
    <div class="d-flex gap-2">
      <a class="btn btn-outline-warning" href="#" title="Histórico">
        <i class="bi bi-clock-history"></i>
      </a>
      ${
        equipament_id
          ? `<a class="btn btn-outline-primary" href="/visualizar-equipamento/?id=${equipament_id}" target="_blank" title="Acessar equipamento utilizador">
              <i class="bi bi-pc-horizontal"></i>
            </a>
            <a class="btn btn-outline-success" href="#" title="Liberar">
              <i class="bi bi-toggle-on"></i>
            </a>`
          : `
            <a class="btn btn-outline-secondary" href="#" title="Utilizar">
              <i class="bi bi-toggle-off"></i>
            </a>`
      }
      <a class="btn btn-outline-secondary" href="/editar-ip/?id=${id}" target="_blank" title="Editar">
        <i class="bi bi-pencil"></i>
      </a>
    </div>`;

  return gridjs.html(html_content);
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

    showAlert("Excluído com sucesso!", "success", true, 3000);

    renderGridJS();
  } catch (error) {
    let error_msg = "[1010]Unknow error on try catch";

    if (error.response?.data?.message) {
      error_msg = error.response.data.message;
    } else {
    }

    showAlert(error_msg, "danger");
  }
}
