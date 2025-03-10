/*
 * LISTENER'S
 */

// console.log(RESERVAS);

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

  const submissions = RESERVAS;

  renderCalendar(submissions);
  removeScrollBar();
}

function removeScrollBar() {
  [...document.querySelectorAll("div.fc-scroller")].forEach((i) => {
    i.style.overflow = "hidden";
  });
}

/**
 * CALENDAR RENDER
 */

function renderCalendar(submissions) {
  console.log(submissions);

  let arr = [];
  for (const submission of submissions) {
    const submission_data = submission["data"];

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
  slotMaxTime: "22:35:00",
  slotDuration: "00:35:00",
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
  dateClick: function (arg) {
    console.log(arg.date.toString()); // use *local* methods on the native Date Object
    // will output something like 'Sat Sep 01 2018 00:00:00 GMT-XX:XX (Eastern Daylight Time)'
  },
});
