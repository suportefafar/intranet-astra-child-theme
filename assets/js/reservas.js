let EVENTS = [];
let CURRENT_CLASSROOM_ID = null;

/*
 * LISTENER'S
 */

/*
 * Aguarda até que a DOM seja carregada para inserir os eventos no calendário
 */
document.addEventListener("DOMContentLoaded", () => {
  loadUI();
});

/*
 * Adicionando redirecionamento dinâmico para impressão do mapa de salas
 */
document
  .querySelector("#btn_print_classroom_map")
  .addEventListener("click", () => {
    const url = "/imprimir-mapa-de-sala/?id=" + CURRENT_CLASSROOM_ID;
    window.open(url, "_blank").focus();
  });

/*
 * Adiciona um evento de clique à DOM,
 * e despara se o elemento que recebeu o clique tem
 * a classe 'btn-loan-equipament' ou é filho de um elemento
 * com essa classe
 */
document
  .querySelector("#btn_event_details_delete")
  .addEventListener("click", (event) => {
    const id = event.target.dataset.id;
    hideEventDetailsModal();
    confirmDelete(id);
  });

/*
 * Adiciona um listener para um evento despachado no formulário
 * de adição de reservas, no CF7, quando a submissão é feita com sucesso
 */
document.addEventListener("onAddEventSuccess", () => {
  // console.log("Evento disparado!");
  hideAddEventModal();
  loadUI();
  showAlert("Reserva adicionada com sucesso!", "success", true);
});

async function loadUI(place_id = false) {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const place = urlParams.get("place");

  if (place_id) {
    CURRENT_CLASSROOM_ID = place_id;
  } else if (CURRENT_CLASSROOM_ID) {
    // não faz nada
  } else if (place) {
    CURRENT_CLASSROOM_ID = place;
  } else {
    CURRENT_CLASSROOM_ID = document.querySelectorAll(
      "#ul_place_tabs .nav-link"
    )[0].dataset.placeId;
  }

  renderPlacesTabs(CURRENT_CLASSROOM_ID);

  const submissions = await getEventsByPlaceID(CURRENT_CLASSROOM_ID);

  renderCalendar(submissions);
}

/*
 * GET EVENTS
 */
async function getEventsByPlaceID(place_id) {
  let response;
  try {
    response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/" +
        place_id +
        "/reservations"
    );

    // console.log({ response });
  } catch (error) {
    // console.log(error.response.data.message);
    return [];
  }

  return JSON.parse(response.data);
}

/**
 * UL TABS RENDER
 */

function renderPlacesTabs(place_id = null) {
  const tabs = document.querySelectorAll("#ul_place_tabs .nav-link");

  tabs.forEach((el) => {
    el.addEventListener("click", onClickPlaceTabHanlder);
  });

  if (place_id) {
    changeActiveTab(place_id);
  }
}

async function onClickPlaceTabHanlder(e) {
  const place_id = e.target.dataset.placeId;

  loadUI(place_id);
}

function changeActiveTab(placeId) {
  const tabs = document.querySelectorAll("#ul_place_tabs .nav-link");

  tabs.forEach((el) => {
    el.classList.remove("active");
    if (el.dataset.placeId === placeId) el.classList.add("active");
  });
}

/**
 * CALENDAR RENDER
 */

const calendarEl = document.getElementById("calendar");

const calendar = new FullCalendar.Calendar(calendarEl, {
  locale: "pt-br",
  headerToolbar: {
    left: "prev,next",
    center: "title",
    right: "multiMonthYear,dayGridMonth,timeGridWeek,timeGridDay,listWeek", // user can switch between the two
  },
  allDaySlot: false,
  initialView: "timeGridWeek",
  views: {
    week: {
      titleFormat: {
        month: "2-digit",
        day: "2-digit",
        year: "numeric",
      },
      eventClassNames: ["cursor-pointer", "text-decoration-none"],
    },
  },
  hiddenDays: [0],
  eventClick: viewEvent,
  dateClick: dateClickHandler,
  events: EVENTS,
});

function renderCalendar(submissions) {
  // console.log(submissions);

  const arr = [];
  for (const submission of submissions) {
    const submission_data = submission["data"];

    arr.push({
      id: submission["id"],
      title: submission_data["title"],
      rrule: submission_data["rrule"].replace(/\\n/g, "\n"),
      duration: submission_data["duration"],
      color: "#F2B600",
    });
  }

  const sources = calendar.getEventSources();

  if (sources.length > 0) sources.forEach((s) => s.remove());

  calendar.addEventSource(arr);

  calendar.refetchEvents();

  calendar.render();
}

function dateClickHandler(info) {
  const { dateStr } = info;

  // console.log("Clicked on: " + dateStr);

  const date_selected = new Date(dateStr);

  document.querySelector("#date").value = parseToDateInputFormat(date_selected);

  document.querySelector("#start_time").value =
    parseToTimeInputFormat(date_selected);

  // Avança os 50 minutos de uma aula padrão
  date_selected.setTime(date_selected.getTime() + 1000 * 60 * 50);

  document.querySelector("#end_time").value =
    parseToTimeInputFormat(date_selected);

  showAddEventModal();
}

async function viewEvent(info) {
  const { id, title, start, end } = info.event;
  // const { cod_usuario } = info.event.extendedProps;
  // const current_user = document.querySelector("#UsuarioID").value;

  const event = await getEventByID(id);

  document.querySelector("#modal_event_title").innerHTML = title;

  document.querySelector("#modal_event_start").innerHTML = new Date(
    start
  ).toLocaleString();

  document.querySelector("#modal_event_end").innerHTML = new Date(
    end
  ).toLocaleString();

  document.querySelector("#modal_event_owner").innerHTML =
    event.owner.data.display_name;

  document.querySelector("#modal_event_applicant").innerHTML = event.data
    .applicant.data
    ? event.data.applicant.data.display_name
    : "--";

  document
    .querySelector("#btn_event_details_info")
    .setAttribute("href", "/visualizar-reserva/?id=" + id);

  document
    .querySelector("#btn_event_details_edit")
    .setAttribute("href", "/editar-reserva/?id=" + id);

  document.querySelector("#btn_event_details_delete").dataset.id = id;

  if (event.data.prevent_write) {
    document.querySelector("#btn_event_details_edit").style.display = "none";
    document.querySelector("#btn_event_details_delete").style.display = "none";
  }

  showEventDetailsModal();

  // console.log(event);
}

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

    // console.log(response);

    showAlert("Excluído com sucesso!", "success", true, 3000);

    loadUI();
  } catch (error) {
    let error_msg = "[1010]Unknow error on try catch";

    if (error.response?.data?.message) {
      // console.log(error.response.data);
      error_msg = error.response.data.message;
    } else {
      // console.log(error);
    }

    showAlert(error_msg, "danger");
  }
}

async function getEventByID(id) {
  let response;
  try {
    response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/reservations/" +
        id
    );
    // console.log({ response });
  } catch (error) {
    // console.log(error.response.data.message);
    return false;
  }
  return JSON.parse(response.data);
}

/*
 * Controle do modal de Detalhes
 */
function showEventDetailsModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarEventDetailsModal")
  );

  modal.show();
}

function hideEventDetailsModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarEventDetailsModal")
  );

  modal.hide();
}

function showAddEventModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarAddEvent")
  );

  modal.show();
}

function hideAddEventModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarAddEvent")
  );

  modal.hide();
}

function parseToDateInputFormat(date) {
  if (!(date instanceof Date)) return null;

  return date.toISOString().split("T")[0];
}

function parseToTimeInputFormat(date) {
  if (!(date instanceof Date)) return null;

  // Extract hours and minutes
  const hours = date.getHours().toString().padStart(2, "0"); // Ensure two digits
  const minutes = date.getMinutes().toString().padStart(2, "0"); // Ensure two digits

  // Format as 'HH:MM'
  return `${hours}:${minutes}`;
}
