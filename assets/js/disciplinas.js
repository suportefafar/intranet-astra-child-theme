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

new gridjs.Grid({
  columns: [
    "ID",
    "Cód.",
    "Nome",
    "Turma",
    "CH",
    "Créditos",
    "Curso",
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
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/object/class_subject"
    );
  } catch (error) {
    console.log(error.response.data.message);
    return [];
  }

  const disciplinas = JSON.parse(response.data);

  let disciplinas_tabela_arr = [];
  for (const disciplina of disciplinas) {
    disciplinas_tabela_arr.push([
      disciplina["id"],
      disciplina["code"],
      disciplina["name_of_subject"],
      disciplina["group"],
      disciplina["course_load"],
      parseInt(disciplina["course_load"]) / 15,
      disciplina["course"],
    ]);
  }

  return disciplinas_tabela_arr;
}

function formatterHandler(_, row) {
  const html_content = `
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href='/vizualizar-objeto/?id=${row.cells[0].data}' title='Detalhes'>
        <i class="bi bi-info-lg"></i>
      </a>
      <a class="btn btn-outline-secondary" href='/editar-disciplina/?id=${row.cells[0].data}' title='Editar'>
        <i class="bi bi-pencil"></i>
      </a>
      <a class="btn btn-outline-danger" href='/excluir-disciplina/?id=${row.cells[0].data}' title='Excluir'>
        <i class="bi bi-trash"></i>
      </a>
      <a class="btn btn-outline-primary" href='/reservas-por-disciplina/?id=${row.cells[0].data}' title='Eventos'>
        <i class="bi bi-calendar-week"></i>
      </a>
    </div>  
      `;

  return html(html_content);
}
