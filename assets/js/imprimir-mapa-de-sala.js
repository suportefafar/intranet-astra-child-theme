/*
 * LISTENER'S
 */

// console.log(RESERVAS);

/*
 * Aguarda até que a DOM seja carregada para inserir os eventos no calendário
 */
document.addEventListener("DOMContentLoaded", () => {
  loadUI();

  document
    .querySelector("#btn_printer")
    .addEventListener("click", () => window.print());
});

async function loadUI() {
  if (!Array.isArray(RESERVAS)) RESERVAS = Object.values(RESERVAS);
  renderCalendar(RESERVAS);
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
  eventBackgroundColor: "transparent",
  eventTextColor: "#000000",
  eventBorderColor: "#000000",
  eventClassNames: "fw-medium",
  dateClick: function (arg) {
    console.log(arg.date.toString()); // use *local* methods on the native Date Object
    // will output something like 'Sat Sep 01 2018 00:00:00 GMT-XX:XX (Eastern Daylight Time)'
  },
  eventContent: function (arg) {
    console.log(arg);

    // Create a container for the event content
    let container = document.createElement("div");

    if (getDurationInMinutes(arg.timeText) > 60) {
      container.style.display = "flex";
      container.style.flexDirection = "column";
    }

    // Add the event time
    let timeEl = document.createElement("span");
    timeEl.style.marginRight = "4px";
    timeEl.innerHTML = arg.timeText; // Event time (e.g., "10:00 AM")

    // Add the event description
    let descEl = document.createElement("span");
    descEl.innerHTML = arg.event._def.title; // Event description

    container.appendChild(timeEl);
    container.appendChild(descEl);

    return { domNodes: [container] };
  },
});

function getDurationInMinutes(timeString) {
  // Split the string into start and end times
  const [startTime, endTime] = timeString.split(" - ");

  // Parse hours and minutes for start time
  const [startHour, startMinute] = startTime.split(":").map(Number);

  // Parse hours and minutes for end time
  const [endHour, endMinute] = endTime.split(":").map(Number);

  // Convert start time to total minutes
  const startTotalMinutes = startHour * 60 + startMinute;

  // Convert end time to total minutes
  const endTotalMinutes = endHour * 60 + endMinute;

  // Calculate duration in minutes
  const durationInMinutes = endTotalMinutes - startTotalMinutes;

  return durationInMinutes;
}
