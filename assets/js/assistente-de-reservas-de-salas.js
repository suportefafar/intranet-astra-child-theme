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
function getFormDataObj() {
  const form = document.querySelector("#form-step-1");

  const formData = new FormData(form); // Create a FormData object from the form

  // Convert to a plain JavaScript object
  const data = Object.fromEntries(formData.entries());
  data.weekdays = getCheckboxesValuesByName("weekdays[]").join(",");

  return data;
}

const btn_search_place = document.querySelector("#btn-search-places");

if (btn_search_place) {
  btn_search_place.addEventListener("click", searchForPlaces);
}

async function searchForPlaces() {
  showAlert("Buscando salas....", "warning", false, 0, true);

  const data = getFormDataObj();

  console.log(data);

  if (!data.date || (data.frequency !== "once" && !data.end_date)) {
    showAlert("Por favor, verifique os dados do formulário", "danger");
    return false;
  }

  let response = {};
  try {
    response = await axios.get(
      "/wp-json/intranet/v1/submissions/place/available-for-reservation",
      { params: data }
    );
    console.log(response);
  } catch (error) {
    console.error(error);
    return;
  }

  const raw_salas = response.data;

  const salas = [];

  for (const sala of raw_salas) {
    const { id, data } = sala;
    const { number, block, floor, capacity, desc } = data;

    const descPlaceCol = JSON.stringify({ id, number, block, floor, desc });

    const actionCol = JSON.stringify({ id, number });

    salas.push([descPlaceCol, capacity, actionCol]);
  }

  document.getElementById("table-wrapper").classList.remove("d-none");

  grid
    .updateConfig({
      search: true,
      data: salas,
    })
    .forceRender();

  hideAlert();

  if (salas.length === 0) showAlert("Nenhuma sala encontrada.", "danger", true);
}

function descPlaceFormatter(current) {
  const { number, block, floor, desc } = JSON.parse(current);

  return gridjs.html(`
    ${number}${desc ? " " + desc + " " : ""}(Bloco: ${block} / Andar: ${floor}º)
  `);
}

function formatterHandler(current) {
  const { id, number } = JSON.parse(current);

  const data = getFormDataObj();

  const queryParams = new URLSearchParams(data);

  queryParams.append("place", JSON.stringify([id]));

  const class_subject = document.querySelector(
    "input[name=class_subject]"
  ).value;

  queryParams.append("class_subject", class_subject);

  const queryString = queryParams.toString();

  // const html_content = `
  // <div class="d-flex gap-2">
  //   <a class="btn btn-outline-primary" href="/assistente-de-reservas-de-salas/?${queryString}" onclick title="Reserva na ${number}">
  //     <i class="bi bi-calendar-week"></i>
  //   </a>
  // </div>
  //     `;

  const html_content = `
  <div class="d-flex gap-2">
    <button class="btn btn-outline-primary btn-select-place" data-place-id=${id} title="Reserva na ${number}">
      <i class="bi bi-calendar-week"></i>
    </button>
  </div>  
      `;

  return gridjs.html(html_content);
}

/*
 * O formulário do passo 1 só pode ser submetido(submit) quando
 * o usuário escolhe uma sala(place). Então....
 */
document.querySelector("#form-step-1").addEventListener("submit", (e) => {
  e.preventDefault();
  console.log("É necessário que uma sala seja escolhida");
});

/*
 * Adiciona um evento de clique à DOM,
 * e despara se o elemento que recebeu o clique tem
 * a classe 'btn-select-place' ou é filho de um elemento
 * com essa classe
 */
document.addEventListener("click", setPlaceAndSubmitForm);

function setPlaceAndSubmitForm(event) {
  const btn_select_place = event.target.closest(".btn-select-place");

  if (!btn_select_place) {
    return false;
  }

  const placeId = btn_select_place.dataset.placeId;
  if (!placeId) {
    alert("ID da sala não encontrado", "error");
    return false;
  }

  const place_hidden_input = document.querySelector("input[name=place]");
  if (!place_hidden_input) {
    alert("Campo de sala não encontrado", "error");
    return false;
  }
  place_hidden_input.value = placeId;

  const form = document.querySelector("#form-step-1");
  if (!form) {
    alert("Formulário(passo 1) não encontrado", "error");
    return false;
  }

  form.submit();
}

// Handle the display of inputs for weekly events
const frequencyRadio = document.querySelector("input[name=frequency]");
frequencyRadio.addEventListener("change", (e) =>
  frequencyInputHandler(e.target.value)
);
frequencyInputHandler(frequencyRadio.value);

function frequencyInputHandler(selectedFrequency) {
  const containerWeekdays = document.querySelector("#container-weekdays");
  const containerEndDate = document.querySelector("#container-end-date");

  if (selectedFrequency && selectedFrequency === "once") {
    containerWeekdays.style.display = "none";
    containerEndDate.style.display = "none";
  } else {
    containerWeekdays.style.display = "block";
    containerEndDate.style.display = "block";
  }
}

// Handle URL query params coming from /reservas-por-disciplina/
function urlQueryParamsHandler() {
  const paramsString = window.location.search;
  const searchParams = new URLSearchParams(paramsString);

  const query_params_capacity = searchParams.get("capacity");
  const query_params_start_time = searchParams.get("start_time");
  const query_params_end_time = searchParams.get("end_time");
  const query_params_weekdays = searchParams.get("weekdays");
  const query_params_frequency = searchParams.get("frequency");
  const query_params_class_subject = searchParams.get("subject");

  console.log({
    query_params_capacity,
    query_params_start_time,
    query_params_end_time,
    query_params_weekdays,
    query_params_frequency,
    query_params_class_subject,
  });

  document.querySelector("input[name=class_subject]").value =
    query_params_class_subject;
  document.querySelector("input[name=start_time]").value =
    query_params_start_time;
  document.querySelector("input[name=end_time]").value = query_params_end_time;
  document.querySelector("input[name=capacity]").value = query_params_capacity;

  const weekdays_arr = query_params_weekdays.split(",");
  changeCheckboxInputValueByName(weekdays_arr, "weekdays[]");

  changeRadioInputValueByName(query_params_frequency, "frequency");

  frequencyInputHandler(query_params_frequency);
}

urlQueryParamsHandler();
