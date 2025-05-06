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
    "Categoria",
    "Fonte",
    "Usuário",
    {
      name: "Desc",
      formatter: (current) =>
        current.length > 50 ? current.slice(0, 50) + "..." : current,
    },
    {
      name: "Registrado em",
      formatter: (current) => new Date(current).toLocaleString(),
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
        let url = `${prev}?limit=${limit}&offset=${page * limit}`;
        if (url.indexOf("keyword") > -1)
          url = `${prev}&limit=${limit}&offset=${page * limit}`;
        console.log(url);
        return url;
      },
    },
    summary: true, // Show pagination summary
  },
  server: {
    url: "/wp-json/intranet/v1/submissions/object/log",
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
    const { id, data, created_at } = submission;
    const { category = "N/A", source = "N/A", user = 0, desc = 0 } = data;

    const prevent_write = data.prevent_write ? "1" : "0";
    const prevent_exec = data.prevent_exec ? "1" : "0";
    const permissions = `${prevent_write}${prevent_exec}`;

    return [
      category,
      source,
      user,
      JSON.stringify(desc),
      created_at,
      JSON.stringify({
        id,
        permissions,
      }),
    ];
  });
}

function actionColFormatter(current) {
  const { id, permissions } = JSON.parse(current);

  const prevent_write = parseInt(permissions.split("")[0]);

  const reservables = [
    "classroom",
    "living_room",
    "computer_lab",
    "multimedia_room",
  ];

  const html_content = `
    <div class="d-flex gap-2">

      <a class="btn btn-outline-secondary" href="/visualizar-objeto/?id=${id}" title="Detalhes">
        <i class="bi bi-info-lg"></i>
      </a>
      
      ${
        prevent_write
          ? ""
          : `

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
  showAlert("Por favor, aguarde....", "warning", false, 0, true);

  try {
    await axios.delete(`/wp-json/intranet/v1/submissions/${id}`);
    showAlert("Excluído com sucesso!", "success", true, 3000);
    renderGridJS();
  } catch (error) {
    const error_msg =
      error.response?.data?.message || "[1010] Erro desconhecido";
    console.error("Erro ao excluir:", error_msg);
    showAlert(error_msg, "danger");
  }
}
