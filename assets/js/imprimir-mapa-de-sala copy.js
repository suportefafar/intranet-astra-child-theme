/*
 * LISTENER'S
 */

/*
 * Aguarda até que a DOM seja carregada para inserir os eventos no calendário
 */
document.addEventListener("DOMContentLoaded", () => {
  loadUI();
});

async function loadUI() {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const place_id = urlParams.get("id");

  const submissions = await getEventsByPlaceID(place_id);

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
    // response = await axios.get(
    //   "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/object/reservation"
    // );
    console.log({ response });
  } catch (error) {
    console.log(error.response.data.message);
    return [];
  }

  return JSON.parse(response.data);
}

/**
 * CALENDAR RENDER
 */

function renderCalendar(submissions) {
  console.log(submissions);

  const arr = [];
  for (const submission of submissions) {
    const submission_data = submission["data"];

    if (submission_data.frequency[0] && submission_data.frequency[0] === "once")
      continue;

    arr.push({
      id: submission["id"],
      title: submission_data["title"],
      rrule: submission_data["rrule"].replace(/\\n/g, "\n"),
      duration: submission_data["duration"],
    });
  }

  const sources = calendar.getEventSources();

  if (sources.length > 0) sources.forEach((s) => s.remove());

  calendar.addEventSource(arr);

  calendar.refetchEvents();

  calendar.render();
}

/*
 * CALENDAR SETTINGS
 */
const calendarEl = document.getElementById("calendar");

const calendar = new FullCalendar.Calendar(calendarEl, {
  locale: "pt-br",
  headerToolbar: {
    left: "prev,next",
    center: "title",
  },
  allDaySlot: false,
  initialView: "timeGridWeek",
  slotMinTime: "07:00:00",
  slotMaxTime: "22:00:00",
  slotDuration: "01:00:00",
  dayHeaderFormat: { weekday: "long" },
  slotLabelFormat: {
    hour: "2-digit",
    minute: "2-digit",
    omitZeroMinute: false,
    meridiem: "long",
  },
  hiddenDays: [0],
  events: [],
  eventColor: "#000000",
  eventClassNames: "fw-medium",
  todayIndicator: false,
});
