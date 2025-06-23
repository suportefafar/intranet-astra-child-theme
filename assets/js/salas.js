/*
 * Adiciona evento de clique para exclusão
 */
document.addEventListener("click", (event) => {
  const deleteButton = event.target.closest(".btn-delete-submission");
  if (deleteButton) confirmDelete(deleteButton.dataset.id);
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
    navigate: (e, r) => `Página ${e} de ${r}`,
    page: (e) => `Página ${e}`,
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
    "Bloco",
    {
      name: "Andar",
      formatter: (current) => current + "ª",
    },
    "Capacidade",
    {
      name: "Tipo",
      formatter: typeColFormatter,
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
    url: "/wp-json/intranet/v1/submissions/object/place",
    then: renderDataOnTable,
    total: (data) => data.count,
  },
  sort: true,
  resizable: true,
  language: ptBR,
}).render(document.getElementById("table-wrapper"));

function renderDataOnTable(data) {
  // Early return if data is invalid or empty
  if (!data || !Array.isArray(data.results)) {
    return [];
  }

  // Map through the results and transform each submission
  return data.results.map((submission) => {
    const { id, data } = submission;
    const {
      number = "N/A",
      desc = "N/A",
      floor = 0,
      block = 0,
      capacity = 0,
      object_sub_type = [],
    } = data;

    const prevent_write = data.prevent_write ? "1" : "0";
    const prevent_exec = data.prevent_exec ? "1" : "0";
    const permissions = `${prevent_write}${prevent_exec}`;

    return [
      number,
      desc,
      block,
      floor,
      parseInt(capacity),
      object_sub_type,
      JSON.stringify({
        id,
        permissions,
        object_sub_type: object_sub_type[0],
        number,
      }),
    ];
  });
}

function typeColFormatter(current) {
  switch (current[0]) {
    case "auditorium":
      return "Auditório";
    case "classroom":
      return "Sala de aula";
    case "general":
      return "Geral";
    case "lab":
      return "Laboratório";
    case "multimedia_room":
      return "Multimídia";
    case "professor_office":
      return "Gabinete";
    case "computer_lab":
      return "Lab de Computador";
    default:
      return "--";
  }
}

function actionColFormatter(current) {
  const { id, permissions, object_sub_type, number } = JSON.parse(current);

  const prevent_write = parseInt(permissions.split("")[0]);

  const reservables = [
    "classroom",
    "living_room",
    "computer_lab",
    "multimedia_room",
  ];

  const html_content = `
    <div class="d-flex gap-2">

      <a class="btn btn-outline-secondary" href="/visualizar-sala/?id=${id}" title="Detalhes">
        <i class="bi bi-info-lg"></i>
      </a>

      ${
        reservables.indexOf(object_sub_type) > -1
          ? `<a class="btn btn-outline-primary" href="/reservas/?place_id=${id}" target="blank" title="Reservas da sala ${number}">
            <i class="bi bi-calendar-week"></i>
          </a>`
          : ""
      }

      ${
        object_sub_type === "auditorium"
          ? `<a class="btn btn-outline-primary" href="/reservas-do-auditorio/" target="blank" title="Reservas do auditório">
            <i class="bi bi-calendar-range"></i>
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

  return gridjs.html(html_content);
}

async function renderGridJS(data = []) {
  if (data.length === 0) data = await fetchDataHandler();

  grid.updateConfig({ data }).forceRender();
}

function confirmDelete(id) {
  showConfirmModal(
    "Excluir Sala?",
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
