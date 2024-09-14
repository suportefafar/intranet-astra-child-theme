import { Grid, html } from "https://unpkg.com/gridjs?module";

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

new gridjs.Grid({
  columns: [
    "ID",
    "Descrição",
    "Sala",
    "Inicio",
    "Fim",
    "Operador",
    "Solicitante",
    {
      name: "Ações",
      formatter: formatterHandler,
    },
  ],
  data: fetchDataHadler,
  pagination: {
    limit: 20,
    summary: true,
  },
  search: true,
  sort: true,
  resizable: true,
  language: ptBR,
}).render(document.getElementById("table-wrapper"));

async function fetchDataHadler() {
  let response;

  try {
    response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/object/evento"
    );
  } catch (error) {
    console.log(error.response.data.message);
    return [];
  }

  const salas = JSON.parse(response.data);

  console.log(salas);
  let salas_tabela_arr = [];
  for (const sala of salas) {
    salas_tabela_arr.push([
      sala["id"],
      sala["descricao"],
      sala["local"],
      sala["iniciohora"],
      sala["fimhora"],
      sala["operador"],
      sala["solicitante"],
    ]);
  }

  return salas_tabela_arr;
}

function formatterHandler(_, row) {
  const html_content = `
  <div class="d-flex gap-2">
    <a class="btn btn-outline-danger" href='/excluir-evento/?id=${row.cells[0].data}'>
      <i class="bi bi-trash"></i>
    </a>
    <a class="btn btn-outline-secondary" href='/vizualizar-objeto/?id=${row.cells[0].data}'>
      <i class="bi bi-ticket-detailed"></i>
    </a>
  </div>  
      `;

  return html(html_content);
}
