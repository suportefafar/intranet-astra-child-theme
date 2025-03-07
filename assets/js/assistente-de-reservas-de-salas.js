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

const gridjs = new Grid({
  columns: [
    { name: "Sala", formatter: descPlaceFormatter },
    "Capacidade",
    {
      name: "Ações",
      formatter: formatterHandler,
    },
  ],
  data: () => [],
  pagination: {
    limit: 20,
    summary: true,
  },
  search: true,
  sort: true,
  resizable: true,
  language: ptBR,
}).render(document.getElementById("table-wrapper"));

/**
 * 'Buscar Salas' FORM HANDLER
 */
const form = document.querySelector("#form-buscar-salas");

form.addEventListener("submit", onSubmitHandler);

async function onSubmitHandler(e) {
  e.preventDefault();

  const date = document.querySelector("#event_date").value;
  const start_time = document.querySelector("#start_time").value;
  const end_time = document.querySelector("#end_time").value;
  const capacity = document.querySelector("#capacity").value;

  const data = { date, start_time, end_time, capacity };

  let response = {};
  try {
    response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/place/available-for-reservation",
      { params: data }
    );
    console.log(response);
  } catch (error) {
    console.log(error);
    return;
  }

  const raw_salas = JSON.parse(response.data);

  const salas = [];
  for (const sala of raw_salas) {
    const { id, data } = sala;
    const { number, block, floor, capacity } = data;

    const descPlaceCol = JSON.stringify({ id, number, block, floor });

    const actionCol = JSON.stringify({ id, number });

    salas.push([descPlaceCol, capacity, actionCol]);
  }

  document.getElementById("table-wrapper").classList.remove("d-none");

  gridjs
    .updateConfig({
      search: true,
      data: salas,
    })
    .forceRender();
}

function descPlaceFormatter(current) {
  const { id, number, block, floor } = JSON.parse(current);

  return html(`
    <a href="/vizualizar-sala?id=${id}" target="blank" title="Detalhes da ${number}">${number}</a> (Bloco: ${block} / Andar: ${floor}º)
  `);
}

function formatterHandler(current) {
  const { id, number } = JSON.parse(current);

  const html_content = `
  <div class="d-flex gap-2">
    <a class="btn btn-outline-primary" href="/reservas/?place_id=${id}" title="Reserva na ${number}">
      <i class="bi bi-calendar-week"></i>
    </a>
  </div>  
      `;

  return html(html_content);
}
