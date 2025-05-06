let EVENTS = [];
let CURRENT_CLASSROOM = { id: null, number: null };

/*
 * LISTENER'S
 */

/*
 * Aguarda até que a DOM seja carregada para inserir os eventos no calendário
 */
document.addEventListener("DOMContentLoaded", () => {
  onLoadCalendarByUrlParams();
  fetchCalendarData();
});

/*
 * Adiciona um listener para cada aba
 */
document.querySelectorAll("#ul_classroom_tabs .nav-link").forEach((el) => {
  el.addEventListener("click", onClickTabHandler);
});

/*
 * Adicionando redirecionamento dinâmico para impressão do mapa de salas
 */
document
  .querySelector("#btn_print_classroom_map")
  .addEventListener("click", () => {
    window.location = "/imprimir-mapa-de-sala/?id=" + CURRENT_CLASSROOM.id;
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
  fetchCalendarData();
  showAlert("Reserva adicionada com sucesso!", "success", true);
});

/*
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
  eventBorderColor: "#000000",
  eventTextColor: "#000000",
});

function onLoadCalendarByUrlParams() {
  const params = new URLSearchParams(document.location.search);
  const place_id = params.get("place_id");

  if (!place_id) return null;

  // Update the current place details
  CURRENT_CLASSROOM.id = place_id;

  // console.log({ place_id });

  // Activate the clicked tab
  changeActiveTab();
}

/*
 * Lida com clique das abas
 */
function onClickTabHandler(e) {
  const target = e.target;

  // Extract necessary data attributes from the clicked element
  const { classroomId, classroomNumber } = target.dataset;

  // Validate that all required attributes exist
  if (!classroomId || !classroomNumber) {
    showAlert(
      "Ocorreu algum erro! Por favor, contate o setor de T.I.",
      "danger"
    );
    console.error(
      "O elemento não contém os dados necessários (classroomId, classroomNumber)."
    );
    return;
  }

  // Update the current place details
  CURRENT_CLASSROOM.id = classroomId;

  // Activate the clicked tab
  changeActiveTab();

  // Fetch and render calendar data
  fetchCalendarData();
}

// Asynchronously fetch calendar data and render the table
async function fetchCalendarData() {
  showAlert("Carregando...", "warning", false, 0, true);
  // console.log("CURRENT_CLASSROOM:", CURRENT_CLASSROOM);

  // If CURRENT_CLASSROOM.id is missing, attempt to set it from the active tab
  if (!CURRENT_CLASSROOM.id) {
    const defaultTab = getActiveTab();
    if (defaultTab) {
      // Here, adjust property names as needed. If your active tab uses classroomId/classroomNumber:
      const { classroomId, classroomNumber } = defaultTab.dataset;
      if (classroomId && classroomNumber) {
        CURRENT_CLASSROOM.id = classroomId;
        CURRENT_CLASSROOM.number = classroomNumber;
      } else {
        showAlert(
          "Ocorreu algum erro! Por favor, contate o setor de T.I.",
          "danger"
        );
        console.error(
          "A aba ativa não contém os dados necessários (classroomId, classroomNumber)."
        );
        return;
      }
    } else {
      showAlert(
        "Ocorreu algum erro! Por favor, contate o setor de T.I.",
        "danger"
      );
      console.error("Nenhuma aba ativa encontrada.");
      return;
    }
  }

  // Assuming getEventsByPlaceID is defined and returns a Promise with the submissions data
  const submissions = await getEventsByPlaceID(CURRENT_CLASSROOM.id);

  renderTable(submissions);
  hideAlert();
}

// Toggle the active class on tabs based on the provided reservation status
function changeActiveTab() {
  const tabs = document.querySelectorAll("#ul_classroom_tabs .nav-link");

  tabs.forEach((tab) => {
    const { classroomId, classroomNumber } = tab.dataset;
    if (classroomId === CURRENT_CLASSROOM.id) {
      tab.classList.add("active");
      CURRENT_CLASSROOM.number = classroomNumber;
    } else {
      tab.classList.remove("active");
    }
  });
}

function getActiveTab() {
  // This will return the first .nav-link with the "active" class (or null if none exist)
  return document.querySelector("#ul_classroom_tabs .nav-link.active");
}

/*
 * GET EVENTS
 */
async function getEventsByPlaceID(place_id) {
  let response;
  try {
    response = await axios.get(
      "/wp-json/intranet/v1/submissions/" + place_id + "/reservations"
    );

    // console.log({ response });
  } catch (error) {
    console.log(error.response.data.message);
    return [];
  }

  return response.data;
}

function renderTable(submissions) {
  // console.log(submissions);

  const arr = [];
  for (const submission of submissions) {
    const submission_data = submission["data"];

    console.log(submission_data);

    let color = "#F2B600";
    if (submission_data.frequency.indexOf("once") > -1) color = "#79ADDC";

    arr.push({
      id: submission["id"],
      title: submission_data["title"],
      rrule: submission_data["rrule"].replace(/\\n/g, "\n"),
      duration: submission_data["duration"],
      color,
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

  // const defaultTab = getActiveTab();
  // const { classroomId, classroomNumber } = defaultTab.dataset;
  // CURRENT_CLASSROOM.id = classroomId;
  // CURRENT_CLASSROOM.number = classroomNumber;

  if (CURRENT_CLASSROOM.number)
    document.querySelector("#place").value = CURRENT_CLASSROOM.number;

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

  document.querySelector("#modal_event_owner").innerHTML = event.owner?.data
    ? event.owner.data.display_name
    : "--";

  document.querySelector("#modal_event_group_owner").innerHTML =
    event.group_owner ? event.group_owner : "--";

  document.querySelector("#modal_event_applicant").innerHTML = event.data
    .applicant?.data
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

  showAlert("Por favor, aguarde....", "warning", false, 0, true);

  try {
    const response = await axios.delete(
      "/wp-json/intranet/v1/submissions/" + id
    );

    // console.log(response);

    showAlert("Excluído com sucesso!", "success", true, 3000);

    fetchCalendarData();
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

async function getEventByID(id) {
  let response;
  try {
    response = await axios.get(
      "/wp-json/intranet/v1/submissions/reservations/" + id
    );
    // console.log({ response });
  } catch (error) {
    console.log(error.response.data.message);
    return false;
  }
  return response.data;
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
